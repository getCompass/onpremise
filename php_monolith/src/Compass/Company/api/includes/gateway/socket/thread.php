<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс-интерфейс для работы с модулем thread
 */
class Gateway_Socket_Thread extends Gateway_Socket_Default {

	/**
	 * очистить все блокировки, если передан user_id чистим только по пользователю
	 *
	 * @param int $user_id
	 *
	 * @return void
	 * @throws \returnException
	 */
	public static function clearAllBlocks(int $user_id = 0):void {

		$params = [];
		if ($user_id > 0) {
			$params["user_id"] = $user_id;
		}

		[$status] = self::doCall("antispam.clearAll", $params);

		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": request to socket method antispam.clearAll is failed");
		}
	}

	/**
	 * добавляем системное сообщение при смене статуса заявки
	 *
	 * @throws \BaseFrame\Exception\Request\CompanyNotServedException
	 * @throws ReturnFatalException
	 * @throws \cs_SocketRequestIsFailed
	 * @long
	 */
	public static function addSystemMessageOnHireRequestStatusChanged(
		string           $thread_map,
		string           $request_type,
		string           $new_status,
		string           $old_status,
		int              $user_id,
		int              $candidate_user_id = 0,
		Struct_User_Info $candidate_info = null
	):void {

		if (mb_strlen($thread_map) < 1) {

			Type_System_Admin::log("add_hiring_system_message", "Not found thread_map of hire request for candidate: {$candidate_user_id}");
			return;
		}

		$ar_post = [
			"thread_map"        => $thread_map,
			"request_type"      => $request_type,
			"new_status"        => $new_status,
			"old_status"        => $old_status,
			"candidate_user_id" => $candidate_user_id,
			"candidate_info"    => (array) $candidate_info,
		];

		try {
			[$status] = self::doCall("threads.addSystemMessageOnHireRequestStatusChanged", $ar_post, $user_id);
		} catch (\cs_SocketRequestIsFailed $e) {

			if ($e->getHttpStatusCode() === 404) {
				throw new \BaseFrame\Exception\Request\CompanyNotServedException("company not served");
			}
			throw $e;
		}

		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": request to socket method threads.addSystemMessageOnHireRequestStatusChanged is failed");
		}
	}

	/**
	 * добавляем системные сообщения в тред заявки на увольнение
	 *
	 * @throws \returnException
	 */
	public static function addSystemMessageToDismissalRequestThread(int $creator_user_id, int $dismissal_user_id, string $thread_map):void {

		$ar_post = [
			"creator_user_id"   => $creator_user_id,
			"dismissal_user_id" => $dismissal_user_id,
			"thread_map"        => $thread_map,
		];
		[$status] = self::doCall("threads.addSystemMessageToDismissalRequestThread", $ar_post, $creator_user_id);

		if ($status != "ok") {
			throw new ReturnFatalException(__METHOD__ . ": request to socket method threads.addSystemMessageToDismissalRequestThread is failed");
		}
	}

	/**
	 * вызовем метод для удаления
	 *
	 * @throws \returnException
	 */
	public static function clearAfterExitUser(string $method, array $ar_post, int $user_id):bool {

		[$status] = self::doCall($method, $ar_post, $user_id);

		// если вернулась ошибка при удалении пользователя
		if ($status != "ok") {

			throw new ReturnFatalException(__METHOD__ . ": request to socket method {$method} is failed");
		}

		return true;
	}

	/**
	 * Выполняет запрос на удаленной вызов скрипта обновления.
	 *
	 * @throws \returnException
	 */
	public static function execCompanyUpdateScript(string $script_name, array $script_data, int $flag_mask):array {

		$params["script_data"] = $script_data;
		$params["script_name"] = $script_name;
		$params["flag_mask"]   = $flag_mask;

		// отправим запрос на удаление из списка
		[$status, $response] = self::doCall("system.execCompanyUpdateScript", $params);

		if ($status != "ok") {
			throw new ReturnFatalException($response["message"]);
		}

		return [$response["script_log"], $response["error_log"]];
	}

	/**
	 * Установить статус компании в конфиге
	 *
	 * @throws \returnException
	 */
	public static function setCompanyStatus(int $status):void {

		$method = "system.setCompanyStatus";

		[$status, $response] = self::doCall($method, ["status" => $status]);

		// если вернулась ошибка при удалении
		if ($status != "ok") {

			throw new ReturnFatalException(__METHOD__ . ": request to socket method {$method} is failed");
		}
	}

	/**
	 * отправляем сообщение в тред
	 *
	 * @throws ReturnFatalException
	 */
	public static function sendMessageToThread(int $userbot_user_id, string $message_key, string $text):void {

		$params = [
			"userbot_user_id" => $userbot_user_id,
			"message_key"     => $message_key,
			"text"            => $text,
		];
		[$status, $response] = self::doCall("userbot.sendMessageToThread", $params);

		// если произошла ошибка
		if ($status != "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}
	}

	/**
	 * отправляем сообщение-Напоминание в тред
	 *
	 * @throws ReturnFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function sendRemindMessage(int $remind_id, string $recipient_id, int $creator_user_id, string $comment, int $remind_type):void {

		$ar_post = [
			"remind_id"       => $remind_id,
			"message_map"     => $recipient_id,
			"comment"         => $comment,
			"remind_type"     => $remind_type,
			"creator_user_id" => $creator_user_id,
		];

		[$status, $response] = self::doCall("threads.sendRemindMessage", $ar_post);

		// если произошла ошибка
		if ($status !== "ok") {

			// работаем над кодом ошибки в ответе
			self::_handleErrorCodeForSendRemindMessage($response["error_code"]);

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}
	}

	/**
	 * ошибки при отправке сообщения-Напоминания
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	protected static function _handleErrorCodeForSendRemindMessage(int $error_code):void {

		switch ($error_code) {

			case 10511:
				throw new \BaseFrame\Exception\Domain\ParseFatalException("duplicate client_message_id");
			case 2418005:
				throw new \BaseFrame\Exception\Domain\ParseFatalException("passed comment text is too long");
		}
	}

	/**
	 * актуализируем данные Напоминания в сообщении-оригинале
	 *
	 * @throws ReturnFatalException
	 * @throws \parseException
	 */
	public static function actualizeTestRemindForMessage(string $recipient_id):void {

		assertTestServer();

		$ar_post = [
			"message_map" => $recipient_id,
		];

		[$status, $response] = self::doCall("threads.actualizeTestRemindForMessage", $ar_post);

		// если произошла ошибка
		if ($status !== "ok") {

			$txt = toJson($response);
			throw new ReturnFatalException("Socket request status != ok. Response: $txt");
		}
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	// получаем подпись из массива параметров
	public static function doCall(string $method, array $params, int $user_id = 0):array {

		// получаем url и подпись
		$url = self::_getSocketCompanyUrl("thread");
		return self::_doCall($url, $method, $params, $user_id);
	}
}
