<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Шлюз для общения с анонсами.
 */
class Gateway_Announcement_Main {

	/** @var string источник запроса */
	protected const _SOURCE = "pivot";

	/**
	 * Публикуем новый анонс.
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function publish(array $announcement):int {

		$ar_post = [
			"announcement" => toJson($announcement),
		];

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("announcement.publish", $ar_post);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}

		return $response["announcement_id"];
	}

	/**
	 * Отмечаем анонс по его типу как недействительный.
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function disable(int $type, int $company_id, array $extra_filter):void {

		$ar_post = [
			"type"         => $type,
			"company_id"   => $company_id,
			"extra_filter" => toJson($extra_filter),
		];

		// отправим запрос на удаление из списка
		[$status, $response] = self::_call("announcement.disable", $ar_post);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}
	}

	/**
	 * Получаем токен пользователя
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getRegisterToken(int $user_id, string $device_id, array $company_list):string {

		[$status, $response] = self::_call("announcement.registerToken", [
			"user_id"      => $user_id,
			"company_list" => $company_list,
			"device_id"    => $device_id,
		]);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code: " . $response["error_code"]);
		}

		return $response["authorization_token"];
	}

	/**
	 * Отвязывает пользователя к компании
	 *
	 */
	public static function bindUserToCompany(int $user_id, int $npc_type, int $company_id):void {

		// если не человек, то дальше не идём
		if (!Type_User_Main::isHuman($npc_type)) {
			return;
		}

		try {

			self::_call("announcement.bindUserToCompany", [
				"user_id"    => $user_id,
				"company_id" => $company_id,
			]);
		} catch (\Exception) {
		}
	}

	/**
	 * Удаляет все записи, связанные с пользователем из анонсов.
	 *
	 */
	public static function invalidateUser(int $user_id):void {

		try {

			self::_call("announcement.invalidateUser", [
				"user_id" => $user_id,
			]);
		} catch (\Exception) {
		}
	}

	/**
	 * Получить список существующих анонсов
	 *
	 */
	public static function getExistingTypeList(int $company_id, array $type_list):array {

		[$status, $response] = self::_call("announcement.getExistingTypeList", [
			"company_id" => $company_id,
			"type_list"  => $type_list,
		]);

		if ($status !== "ok") {
			throw new \BaseFrame\Exception\Domain\ReturnFatalException("passed unknown error_code: " . $response["error_code"]);
		}

		return $response["existing_type_list"];
	}

	# region protected

	/**
	 * Делаем запрос в сервис анонсов.
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _call(string $method, array $params):array {

		// устанавливаем источник запроса для подписи ключа
		$params["source"] = static::_SOURCE;

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$url       = self::_getUrl();
		$signature = Type_Socket_Auth_Handler::getSignature(Type_Socket_Auth_Handler::AUTH_TYPE_KEY, GLOBAL_ANNOUNCEMENT_PRIVATE_KEY, $json_params);

		return Type_Socket_Main::doCall($url, $method, $json_params, $signature);
	}

	/**
	 * Делаем url сервиса анонсов.
	 *
	 */
	protected static function _getUrl():string {

		$socket_module_config = getConfig("SOCKET_URL");
		$socket_path_config   = getConfig("SOCKET_MODULE");

		return $socket_module_config["announcement"] . $socket_path_config["announcement"]["socket_path"];
	}

	# endregion protected
}
