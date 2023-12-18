<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Request\CaseException;

/**
 * Class Apiv3_Message
 */
class Apiv3_Message extends Apiv3_Default {

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
			Domain_Message_Scenario_Api_V3::addReaction($this->token, $message_id, $reaction);
		} catch (\cs_Userbot_RequestIncorrect $e) {
			throw new CaseException(CASE_EXCEPTION_CODE_1000, "invalid parameters passed: " . $e->getMessage());
		} catch (\cs_Userbot_NotFound) {
			throw new CaseException(CASE_EXCEPTION_CODE_2, "userbot is not found");
		} catch (\cs_Userbot_IsNotEnabled) {
			throw new CaseException(CASE_EXCEPTION_CODE_3, "userbot is disabled or deleted");
		} catch (Domain_Userbot_Exception_RequestFailed $e) {
			throw new CaseException((int) $e->getFailedData()["error_code"], $e->getFailedData()["message"]);
		} catch (\Exception | \Error) {
			throw new CaseException(CASE_EXCEPTION_CODE_6, "unknown error while executing internal method for query in progress");
		}

		return $this->ok();
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
			Domain_Message_Scenario_Api_V3::removeReaction($this->token, $message_id, $reaction);
		} catch (\cs_Userbot_RequestIncorrect $e) {
			throw new CaseException(CASE_EXCEPTION_CODE_1000, "invalid parameters passed: " . $e->getMessage());
		} catch (\cs_Userbot_NotFound) {
			throw new CaseException(CASE_EXCEPTION_CODE_2, "userbot is not found");
		} catch (\cs_Userbot_IsNotEnabled) {
			throw new CaseException(CASE_EXCEPTION_CODE_3, "userbot is disabled or deleted");
		} catch (Domain_Userbot_Exception_RequestFailed $e) {
			throw new CaseException((int) $e->getFailedData()["error_code"], $e->getFailedData()["message"]);
		} catch (\Exception | \Error) {
			throw new CaseException(CASE_EXCEPTION_CODE_6, "unknown error while executing internal method for query in progress");
		}

		return $this->ok();
	}
}