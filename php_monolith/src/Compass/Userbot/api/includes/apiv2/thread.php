<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Request\CaseException;

/**
 * Class Apiv2_Thread
 */
class Apiv2_Thread extends Apiv2_Default {

	public const ALLOW_METHODS = [
		"send",
	];

	/**
	 * метод для отправки сообщения
	 *
	 * @throws CaseException
	 */
	public function send():array {

		$message_id = $this->post(Type_Formatter::TYPE_STRING, "message_id");
		$text       = $this->post(Type_Formatter::TYPE_STRING, "text", false);
		$file_id    = $this->post(Type_Formatter::TYPE_STRING, "file_id", false);
		$type       = $this->post(Type_Formatter::TYPE_STRING, "type");

		try {
			$request_id = Domain_Thread_Scenario_Api_V2::send($this->token, $message_id, $type, $text, $file_id);
		} catch (\cs_Userbot_RequestIncorrect $e) {
			throw new CaseException(CASE_EXCEPTION_CODE_1000, "invalid parameters passed: " . $e->getMessage());
		} catch (\cs_Userbot_NotFound) {
			throw new CaseException(CASE_EXCEPTION_CODE_2, "userbot is not found");
		} catch (\cs_Userbot_IsNotEnabled) {
			throw new CaseException(CASE_EXCEPTION_CODE_3, "userbot is disabled or deleted");
		} catch (\Exception | \Error) {
			throw new CaseException(CASE_EXCEPTION_CODE_6, "unknown error while executing internal method for query in progress");
		}

		return $this->ok([
			"request_id" => (string) $request_id,
		]);
	}
}