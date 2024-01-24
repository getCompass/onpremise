<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Request\CaseException;

/**
 * Class Apiv3_Command
 */
class Apiv3_Command extends Apiv3_Default {

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
			Domain_Command_Scenario_Api_V3::update($this->token, $command_list);
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
	 * метод для получения списка команд бота
	 *
	 * @throws CaseException
	 */
	public function getList():array {

		try {
			$command_list = Domain_Command_Scenario_Api_V3::getList($this->token);
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
			"command_list" => Apiv3_Format::userbotCommandList($command_list)
		]);
	}
}