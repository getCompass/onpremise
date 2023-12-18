<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Request\CaseException;

/**
 * Class Apiv1_File
 */
class Apiv1_File extends Apiv1_Default {

	public const ALLOW_METHODS = [
		"getUrl",
	];

	/**
	 * метод для получения данных для загрузки файла
	 *
	 * @throws CaseException
	 */
	public function getUrl():array {

		try {
			$request_id = Domain_File_Scenario_Api_V1::getUrl($this->token);
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