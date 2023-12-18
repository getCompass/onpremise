<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Request\CaseException;

/**
 * Class Apiv2_Webhook
 */
class Apiv2_Webhook extends Apiv2_Default {

	public const ALLOW_METHODS = [
		"setVersion",
		"getVersion",
	];

	/**
	 * метод для установки версии webhook боту
	 *
	 * @throws CaseException
	 */
	public function setVersion():array {

		$version = $this->post(Type_Formatter::TYPE_INT, "version");

		try {
			$request_id = Domain_Webhook_Scenario_Api_V2::setVersion($this->token, $version);
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
	 * метод для получения версии webhook бота
	 *
	 * @throws CaseException
	 */
	public function getVersion():array {

		try {
			$version = Domain_Webhook_Scenario_Api_V2::getVersion($this->token);
		} catch (\cs_Userbot_NotFound) {
			throw new CaseException(CASE_EXCEPTION_CODE_2, "userbot is not found");
		} catch (\cs_Userbot_IsNotEnabled) {
			throw new CaseException(CASE_EXCEPTION_CODE_3, "userbot is disabled or deleted");
		} catch (\Exception | \Error) {
			throw new CaseException(CASE_EXCEPTION_CODE_6, "unknown error while executing internal method for query in progress");
		}

		return $this->ok([
			"version" => (int) $version,
		]);
	}
}