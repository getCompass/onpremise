<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Request\CaseException;

/**
 * Class Apiv3_Group
 */
class Apiv3_Group extends Apiv3_Default {

	public const ALLOW_METHODS = [
		"send",
		"getList",
	];

	/**
	 * метод для отправки сообщения
	 *
	 * @throws CaseException
	 */
	public function send():array {

		$group_id = $this->post(Type_Formatter::TYPE_STRING, "group_id");
		$text     = $this->post(Type_Formatter::TYPE_STRING, "text", false);
		$file_id  = $this->post(Type_Formatter::TYPE_STRING, "file_id", false);
		$type     = $this->post(Type_Formatter::TYPE_STRING, "type");

		try {
			$message_id = Domain_Group_Scenario_Api_V3::send($this->token, $group_id, $type, $text, $file_id);
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

		return $this->ok([
			"message_id" => (string) $message_id,
		]);
	}

	/**
	 * метод для получения списка групп бота
	 *
	 * @throws CaseException
	 */
	public function getList():array {

		$count  = $this->post(Type_Formatter::TYPE_INT, "count", Domain_Group_Entity_Validator::GET_GROUPS_COUNT_DEFAULT);
		$offset = $this->post(Type_Formatter::TYPE_INT, "offset", Domain_Group_Entity_Validator::GET_GROUPS_OFFSET_DEFAULT);

		try {

			$group_info_list = Domain_Group_Scenario_Api_V3::getList($this->token, $count, $offset);
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

		return $this->ok([
			"group_list" => Apiv1_Format::groupInfoList($group_info_list),
		]);
	}
}