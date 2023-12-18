<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Request\CaseException;

/**
 * Class Apiv1_Command
 */
class Apiv1_Command extends Apiv1_Default {

	public const ALLOW_METHODS = [
		"update",
		"getList",
	];

	/**
	 * метод для обновления списка команд бота
	 *
	 * @throws CaseException
	 */
	public function update():array {

		$command_list = $this->post(Type_Formatter::TYPE_ARRAY, "command_list");

		try {
			$request_id = Domain_Command_Scenario_Api_V1::update($this->token, $command_list);
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
	 * метод для получения списка команд бота
	 *
	 * @throws CaseException
	 */
	public function getList():array {

		try {
			$request_id = Domain_Command_Scenario_Api_V1::getList($this->token);
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