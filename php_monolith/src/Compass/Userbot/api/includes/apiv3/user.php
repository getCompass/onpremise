<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Request\CaseException;

/**
 * класс для работы с пользователям
 */
class Apiv3_User extends Apiv3_Default {

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

		$user_id = $this->post(Type_Formatter::TYPE_INT, "user_id");
		$text    = $this->post(Type_Formatter::TYPE_STRING, "text", false);
		$file_id = $this->post(Type_Formatter::TYPE_STRING, "file_id", false);
		$type    = $this->post(Type_Formatter::TYPE_STRING, "type");

		try {
			$message_id = Domain_User_Scenario_Api_V3::send($this->token, $user_id, $type, $text, $file_id);
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
	 * получаем список данных о пользователях компании
	 *
	 * @throws CaseException
	 */
	public function getList():array {

		$count  = $this->post(Type_Formatter::TYPE_INT, "count", Domain_User_Entity_Validator::GET_USERS_COUNT_DEFAULT);
		$offset = $this->post(Type_Formatter::TYPE_INT, "offset", Domain_User_Entity_Validator::GET_USERS_OFFSET_DEFAULT);

		try {
			$user_list = Domain_User_Scenario_Api_V3::getList($this->token, $count, $offset);
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
			"user_list" => (array) Apiv3_Format::userInfoList($user_list),
		]);
	}
}