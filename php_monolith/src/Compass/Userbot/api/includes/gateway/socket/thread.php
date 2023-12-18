<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * задача класса общаться между php_userbot -> php_thread
 */
class Gateway_Socket_Thread {

	/**
	 * отправить сообщение в тред
	 *
	 * @throws \cs_Message_IsNotAllowed
	 * @throws \cs_Message_IsNotFound
	 * @throws \cs_Userbot_RequestIncorrectParams
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	public static function sendMessageToThread(int $userbot_user_id, string $message_key, string|false $text, string|false $file_key, string $company_url):string {

		// формируем параметры для запроса
		$params = [
			"userbot_user_id" => $userbot_user_id,
			"message_key"     => $message_key,
		];

		if ($text !== false) {
			$params["text"] = $text;
		}

		if ($file_key !== false) {
			$params["file_key"] = $file_key;
		}

		[$status, $response] = self::_call("userbot.sendMessageToThread", $params, $company_url);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("passed unknown error_code");
			}

			switch ($response["error_code"]) {

				case 10014: // ошибка, что текст или ключ файла некорректны
					throw new \cs_Userbot_RequestIncorrectParams();
				case 10015: // ошибка, что у бота нет доступа к сообщению
					throw new \cs_Message_IsNotAllowed();
				case 10022: // ошибка, что сообщение не найдено
				case 10023:
					throw new \cs_Message_IsNotFound();
			}
		}

		return $response["message_key"];
	}

	/**
	 * добавить реакцию на сообщение треда
	 *
	 * @throws \cs_Message_IsNotAllowed
	 * @throws \cs_Reaction_IsNotFound
	 * @throws \cs_Userbot_RequestIncorrectParams
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function addReaction(int $userbot_user_id, string $message_key, string $reaction, string $company_url):void {

		// формируем параметры для запроса
		$params = [
			"userbot_user_id" => $userbot_user_id,
			"message_key"     => $message_key,
			"reaction"        => $reaction,
		];

		[$status, $response] = self::_call("userbot.addReaction", $params, $company_url);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("passed unknown error_code");
			}

			switch ($response["error_code"]) {

				case 10016: // ошибка, что реакция или ключ сообщения некорректны
					throw new \cs_Userbot_RequestIncorrectParams();
				case 10017: // ошибка, что такой реакции не существует
					throw new \cs_Reaction_IsNotFound();
				case 10018: // ошибка, что у бота нет доступа к сообщению
					throw new \cs_Message_IsNotAllowed();
			}
		}
	}

	/**
	 * удалить реакцию с сообщения треда
	 *
	 * @throws \cs_Message_IsNotAllowed
	 * @throws \cs_Reaction_IsNotFound
	 * @throws \cs_Userbot_RequestIncorrectParams
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function removeReaction(int $userbot_user_id, string $message_key, string $reaction, string $company_url):void {

		// формируем параметры для запроса
		$params = [
			"userbot_user_id" => $userbot_user_id,
			"message_key"     => $message_key,
			"reaction"        => $reaction,
		];

		[$status, $response] = self::_call("userbot.removeReaction", $params, $company_url);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("passed unknown error_code");
			}

			switch ($response["error_code"]) {

				case 10019: // ошибка, что реакция или ключ сообщения некорректны
					throw new \cs_Userbot_RequestIncorrectParams();
				case 10020: // ошибка, что такой реакции не существует
					throw new \cs_Reaction_IsNotFound();
				case 10021: // ошибка, что у бота нет доступа к сообщению
					throw new \cs_Message_IsNotAllowed();
			}
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
	 * @param int    $user_id
	 * @param int    $company_id
	 * @param string $company_url
	 * @param string $private_key
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
		return [$domino_entrypoint_config[$domino] . $socket_module_config["thread"]["socket_path"], $company_id];
	}
}
