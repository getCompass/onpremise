<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * задача класса общаться между php_userbot -> php_conversation
 */
class Gateway_Socket_Conversation {

	/**
	 * отправить сообщение пользователю
	 *
	 * @throws \\cs_Member_IsKicked
	 * @throws \\cs_Member_IsNotFound
	 * @throws \cs_Userbot_RequestIncorrectParams
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	public static function sendMessageToUser(int $userbot_user_id, int $user_id, string|false $text, string|false $file_key, string $domino_entrypoint, int $company_id):string {

		// формируем параметры для запроса
		$params = [
			"userbot_user_id" => $userbot_user_id,
			"user_id"         => $user_id,
		];

		if ($text !== false) {
			$params["text"] = $text;
		}

		if ($file_key !== false) {
			$params["file_key"] = $file_key;
		}

		[$status, $response] = self::_call("userbot.sendMessageToUser", $params, $domino_entrypoint, $company_id);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("passed unknown error_code");
			}

			switch ($response["error_code"]) {

				case 10000: // ошибка, что текст или file_key некорректен
					throw new \cs_Userbot_RequestIncorrectParams();
				case 10001: // ошибка, что пользователь не существует
					throw new \cs_Member_IsNotFound();
				case 10002: // ошибка, что пользователь уволен
					throw new \cs_Member_IsKicked();
			}
		}

		return $response["message_key"];
	}

	/**
	 * отправить сообщение в диалог
	 *
	 * @throws \cs_Conversation_IsNotAllowed
	 * @throws \cs_Conversation_IsNotFound
	 * @throws \cs_Conversation_IsNotGroup
	 * @throws \cs_Userbot_RequestIncorrectParams
	 * @throws \parseException
	 * @throws \returnException
	 * @long
	 */
	public static function sendMessageToGroup(int $userbot_user_id, string $conversation_key, string|false $text, string|false $file_key, string $domino_entrypoint, int $company_id):string {

		// формируем параметры для запроса
		$params = [
			"userbot_user_id"  => $userbot_user_id,
			"conversation_key" => $conversation_key,
		];

		if ($text !== false) {
			$params["text"] = $text;
		}

		if ($file_key !== false) {
			$params["file_key"] = $file_key;
		}

		[$status, $response] = self::_call("userbot.sendMessageToGroup", $params, $domino_entrypoint, $company_id);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("passed unknown error_code");
			}

			switch ($response["error_code"]) {

				case 10003: // ошибка, что текст, file_key или conversation_key некорректен
					throw new \cs_Userbot_RequestIncorrectParams();
				case 10004: // ошибка, что диалог не групповой
					throw new \cs_Conversation_IsNotGroup();
				case 10005: // ошибка, что у бота нет доступа к группе
					throw new \cs_Conversation_IsNotAllowed();
				case 10023: // ошибка, что такой диалог не найден
					throw new \cs_Conversation_IsNotFound();
			}
		}

		return $response["message_key"];
	}

	/**
	 * добавить реакцию на сообщение диалога
	 *
	 * @throws \cs_Message_IsNotAllowed
	 * @throws \cs_Reaction_IsNotFound
	 * @throws \cs_Userbot_RequestIncorrectParams
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function addReaction(int $userbot_user_id, string $message_key, string $reaction, string $domino_entrypoint, int $company_id):void {

		// формируем параметры для запроса
		$params = [
			"userbot_user_id" => $userbot_user_id,
			"message_key"     => $message_key,
			"reaction"        => $reaction,
		];

		[$status, $response] = self::_call("userbot.addReaction", $params, $domino_entrypoint, $company_id);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("passed unknown error_code");
			}

			switch ($response["error_code"]) {

				case 10006: // ошибка, что реакция или ключ сообщения некорректны
					throw new \cs_Userbot_RequestIncorrectParams();
				case 10007: // ошибка, что такой реакции не существует
					throw new \cs_Reaction_IsNotFound();
				case 10008: // ошибка, что у бота нет доступа к сообщению
					throw new \cs_Message_IsNotAllowed();
			}
		}
	}

	/**
	 * удалить реакцию с сообщения диалога
	 *
	 * @throws \cs_Message_IsNotAllowed
	 * @throws \cs_Reaction_IsNotFound
	 * @throws \cs_Userbot_RequestIncorrectParams
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function removeReaction(int $userbot_user_id, string $message_key, string $reaction, string $domino_entrypoint, int $company_id):void {

		// формируем параметры для запроса
		$params = [
			"userbot_user_id" => $userbot_user_id,
			"message_key"     => $message_key,
			"reaction"        => $reaction,
		];

		[$status, $response] = self::_call("userbot.removeReaction", $params, $domino_entrypoint, $company_id);

		if ($status != "ok") {

			if (!isset($response["error_code"])) {
				throw new ReturnFatalException("passed unknown error_code");
			}

			switch ($response["error_code"]) {

				case 10009: // ошибка, что реакция или ключ сообщения некорректны
					throw new \cs_Userbot_RequestIncorrectParams();
				case 10010: // ошибка, что такой реакции не существует
					throw new \cs_Reaction_IsNotFound();
				case 10011: // ошибка, что у бота нет доступа к сообщению
					throw new \cs_Message_IsNotAllowed();
			}
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * делаем вызов
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
		return $domino_entrypoint . $socket_module_config["conversation"]["socket_path"];
	}
}
