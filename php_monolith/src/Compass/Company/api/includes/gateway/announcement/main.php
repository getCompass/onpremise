<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Шлюз для общения с анонсами.
 */
class Gateway_Announcement_Main {

	/** @var string источник запроса */
	protected const _SOURCE = "company";

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
	 * Привязывает пользователя к компании
	 */
	public static function unbindUserFromCompany(int $user_id, int $company_id):void {

		$ar_post = [
			"user_id"    => $user_id,
			"company_id" => $company_id,
		];

		try {
			self::_call("announcement.unbindUserFromCompany", $ar_post);
		} catch (\Exception) {
		}
	}

	/**
	 * Меняем у анонса список получателей
	 * Поменяется только у УНИКАЛЬНЫХ анонсов
	 */
	public static function changeReceiverUserList(array $announcement_type_list, array $add_user_id_list = [], array $remove_user_id_list = []):void {

		$ar_post = [
			"announcement_type_list" => $announcement_type_list,
			"add_user_id_list"       => $add_user_id_list,
			"remove_user_id_list"    => $remove_user_id_list,
			"company_id"             => COMPANY_ID,
		];

		try {
			self::_call("announcement.changeReceiverUserList", $ar_post);
		} catch (\Exception) {

			Type_System_Admin::log(
				"announcement_gateway", "Не смогли изменить список получателей в анонсе для пространства {$ar_post["company_id"]}", true);
		}
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
		$signature = \BaseFrame\Socket\Authorization\Handler::getSignature(\BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_KEY, COMPANY_ANNOUNCEMENT_PRIVATE_KEY, $json_params);

		return \BaseFrame\Socket\Main::doCall($url, $method, $json_params, $signature, CURRENT_MODULE, COMPANY_ID);
	}

	/**
	 * Делаем url сервиса анонсов.
	 */
	protected static function _getUrl():string {

		$socket_module_config = getConfig("SOCKET_URL");
		$socket_path_config   = getConfig("SOCKET_MODULE");

		return $socket_module_config["announcement"] . $socket_path_config["announcement"]["socket_path"];
	}

	# endregion protected
}
