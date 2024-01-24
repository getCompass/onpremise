<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Request\CaseException;

/**
 * Class Apiv1_User
 */
class Apiv1_User extends Apiv1_Default {

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

		$user_id = $this->post(Type_Formatter::TYPE_STRING, "user_id");
		$text    = $this->post(Type_Formatter::TYPE_STRING, "text", false);
		$file_id = $this->post(Type_Formatter::TYPE_STRING, "file_id", false);
		$type    = $this->post(Type_Formatter::TYPE_STRING, "type");

		try {

			// преобразуем старую версию user_id формата "User-{ID}" в int-значение
			$user_id = Domain_Userbot_Action_ConvertServiceUserId::from($user_id);

			$request_id = Domain_User_Scenario_Api_V1::send($this->token, $user_id, $type, $text, $file_id);
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
	 * получаем список данных о пользователях компании
	 *
	 * @throws CaseException
	 */
	public function getList():array {

		$count  = $this->post(Type_Formatter::TYPE_INT, "count", Domain_User_Entity_Validator::GET_USERS_COUNT_DEFAULT);
		$offset = $this->post(Type_Formatter::TYPE_INT, "offset", Domain_User_Entity_Validator::GET_USERS_OFFSET_DEFAULT);

		try {
			$request_id = Domain_User_Scenario_Api_V1::getList($this->token, $count, $offset);
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