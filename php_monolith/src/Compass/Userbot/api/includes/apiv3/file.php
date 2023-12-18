<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Request\CaseException;

/**
 * Class Apiv3_File
 */
class Apiv3_File extends Apiv3_Default {

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
			[$node_url, $file_token] = Domain_File_Scenario_Api_V3::getUrl($this->token);
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
			"node_url"   => (string) Apiv3_Format::fileNodeUrl($node_url),
			"file_token" => (string) $file_token,
		]);
	}
}