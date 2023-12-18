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
	public static function updateCommandList(string $userbot_id, array $command_list, string $company_url):void {

		// формируем параметры для запроса
		$params = [
			"userbot_id"   => $userbot_id,
			"command_list" => $command_list,
		];

		[$status, $response] = self::_call("company.userbot.updateCommandList", $params, $company_url);

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
	public static function getCommandList(string $userbot_id, string $company_url):array {

		// формируем параметры для запроса
		$params = [
			"userbot_id" => $userbot_id,
		];

		[$status, $response] = self::_call("company.userbot.getCommandList", $params, $company_url);

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
	public static function getGroupList(string $userbot_id, string $company_url):array {

		// формируем параметры для запроса
		$params = [
			"userbot_id" => $userbot_id,
		];

		[$status, $response] = self::_call("company.userbot.getGroupList", $params, $company_url);

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
	public static function getUserList(string $userbot_id, int $count, int $offset, string $company_url):array {

		// формируем параметры для запроса
		$params = [
			"userbot_id" => $userbot_id,
			"count"      => $count,
			"offset"     => $offset,
		];

		[$status, $response] = self::_call("company.userbot.getUserList", $params, $company_url);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}

		return $response["user_list"];
	}

	/**
	 * добавляем реакцию на сообщение
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function addReaction(int $userbot_user_id, string $message_key, string $reaction, string $company_url):void {

		// формируем параметры для запроса
		$params = [
			"user_id"     => $userbot_user_id,
			"message_key" => $message_key,
			"reaction"    => $reaction,
		];

		[$status] = self::_call("company.userbot.addReaction", $params, $company_url);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}
	}

	/**
	 * удаляем реакцию с сообщения
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function removeReaction(int $userbot_user_id, string $message_key, string $reaction, string $company_url):void {

		// формируем параметры для запроса
		$params = [
			"user_id"     => $userbot_user_id,
			"message_key" => $message_key,
			"reaction"    => $reaction,
		];

		[$status] = self::_call("company.userbot.removeReaction", $params, $company_url);

		if ($status != "ok") {
			throw new ParseFatalException("passed unknown error_code");
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * делаем вызов в php_company
	 *
	 * @param string $method
	 * @param array  $params
	 * @param string $company_url
	 *
	 * @return array
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _call(string $method, array $params, string $company_url):array {

		[$entrypoint_url, $company_id] = self::_getCompanyEntrypoint($company_url);

		// переводим в json параметры
		$json_params = toJson($params);

		// получаем url и подпись
		$signature = Type_Socket_Auth_Handler::getSignature(Type_Socket_Auth_Handler::AUTH_TYPE_KEY, COMPANY_USERBOT_PRIVATE_KEY, $json_params);

		return Type_Socket_Main::doCall($entrypoint_url, $method, $json_params, $signature, $company_id);
	}

	/**
	 * Получаем entrypoint компании
	 *
	 * @param string $company_url
	 *
	 * @return array
	 */
	public static function _getCompanyEntrypoint(string $company_url):array {

		// если компании в заголовке нет - возьмем из домена
		$company_domino           = explode(".", $company_url)[0];
		$company_domino_explode   = explode("-", $company_domino);
		$domino                   = $company_domino_explode[1];
		$domino_entrypoint_config = getConfig("DOMINO_ENTRYPOINT");
		$socket_module_config     = getConfig("SOCKET_MODULE");
		$company_id               = intval(substr($company_domino_explode[0], 1));
		return [$domino_entrypoint_config[$domino] . $socket_module_config["company"]["socket_path"], $company_id];
	}
}
