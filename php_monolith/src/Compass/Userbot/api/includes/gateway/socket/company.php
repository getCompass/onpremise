<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * задача класса общаться между php_userbot -> php_company
 */
class Gateway_Socket_Company {

	/**
	 * обновляем список команд для бота
	 *
	 * @throws \cs_UserbotCommand_IsIncorrect
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function updateCommandList(string $userbot_id, array $command_list, string $domino_entrypoint, int $company_id):void {

		// формируем параметры для запроса
		$params = [
			"userbot_id"   => $userbot_id,
			"command_list" => $command_list,
		];

		[$status, $response] = self::_call("company.userbot.updateCommandList", $params, $domino_entrypoint, $company_id);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("passed unknown error_code");
			}

			switch ($response["error_code"]) {

				case 10012: // ошибка, что одна из комманд некорректна
					throw new \cs_UserbotCommand_IsIncorrect();
			}
		}
	}

	/**
	 * получаем список команд бота
	 *
	 * @throws ParseFatalException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getCommandList(string $userbot_id, string $domino_entrypoint, int $company_id):array {

		// формируем параметры для запроса
		$params = [
			"userbot_id" => $userbot_id,
		];

		[$status, $response] = self::_call("company.userbot.getCommandList", $params, $domino_entrypoint, $company_id);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["command_list"];
	}

	/**
	 * получение списка групп бота
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getGroupList(string $userbot_id, string $domino_entrypoint, int $company_id):array {

		// формируем параметры для запроса
		$params = [
			"userbot_id" => $userbot_id,
		];

		[$status, $response] = self::_call("company.userbot.getGroupList", $params, $domino_entrypoint, $company_id);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["group_info_list"];
	}

	/**
	 * получение списка данных участников компании
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function getUserList(string $userbot_id, int $count, int $offset, string $domino_entrypoint, int $company_id):array {

		// формируем параметры для запроса
		$params = [
			"userbot_id" => $userbot_id,
			"count"      => $count,
			"offset"     => $offset,
		];

		[$status, $response] = self::_call("company.userbot.getUserList", $params, $domino_entrypoint, $company_id);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["user_list"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * делаем вызов в php_company
	 *
	 */
	protected static function _call(string $method, array $params, string $domino_entrypoint, int $company_id):array {

		$entrypoint_url = self::_getCompanyEntrypoint($domino_entrypoint);

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$signature = Type_Socket_Auth_Handler::getSignature(Type_Socket_Auth_Handler::AUTH_TYPE_KEY, COMPANY_USERBOT_PRIVATE_KEY, $json_params);

		return Type_Socket_Main::doCall($entrypoint_url, $method, $json_params, $signature, $company_id);
	}

	/**
	 * Получаем entrypoint компании
	 *
	 */
	public static function _getCompanyEntrypoint(string $domino_entrypoint):string {

		// если компании в заголовке нет - возьмем из домена
		$socket_module_config = getConfig("SOCKET_MODULE");
		return $domino_entrypoint . $socket_module_config["company"]["socket_path"];
	}
}
