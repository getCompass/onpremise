<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Request\CaseException;

/**
 * Class Apiv1_Message
 */
class Apiv1_Message extends Apiv1_Default {

	public const ALLOW_METHODS = [
		"addReaction",
		"removeReaction",
	];

	/**
	 * метод для добавления реакции на сообщение
	 *
	 * @throws CaseException
	 */
	public function addReaction():array {

		$message_id = $this->post(Type_Formatter::TYPE_STRING, "message_id");
		$reaction   = $this->post(Type_Formatter::TYPE_STRING, "reaction");

		try {
			$request_id = Domain_Message_Scenario_Api_V1::addReaction($this->token, $message_id, $reaction);
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

	/**
	 * метод для удаления реакции с сообщения
	 *
	 * @throws CaseException
	 */
	public function removeReaction():array {

		$message_id = $this->post(Type_Formatter::TYPE_STRING, "message_id");
		$reaction   = $this->post(Type_Formatter::TYPE_STRING, "reaction");

		try {
			$request_id = Domain_Message_Scenario_Api_V1::removeReaction($this->token, $message_id, $reaction);
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