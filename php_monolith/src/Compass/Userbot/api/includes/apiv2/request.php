<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Request\CaseException;

/**
 * Class Apiv2_Request
 */
class Apiv2_Request extends Apiv2_Default {

	public const ALLOW_METHODS = [
		"get",
	];

	/**
	 * метод для получения данных выполнения запроса внешнего сервиса
	 *
	 * @throws CaseException
	 */
	public function get():array {

		$request_id = $this->post(Type_Formatter::TYPE_STRING, "request_id");

		try {
			[$status, $result_data] = Domain_Request_Scenario_Api::get($this->token, $request_id);
		} catch (\cs_Userbot_RequestIncorrect | \cs_RowIsEmpty $e) {
			throw new CaseException(CASE_EXCEPTION_CODE_1000, "invalid parameters passed: " . $e->getMessage());
		} catch (\cs_Userbot_NotFound) {
			throw new CaseException(CASE_EXCEPTION_CODE_2, "userbot is not found");
		} catch (\cs_Userbot_IsNotEnabled) {
			throw new CaseException(CASE_EXCEPTION_CODE_3, "userbot is disabled or deleted");
		} catch (\Exception | \Error) {
			throw new CaseException(CASE_EXCEPTION_CODE_6, "unknown error while executing internal method for query in progress");
		}

		// если запрос ещё выполняется
		if ($status == Domain_Request_Entity_Request::STATUS_NEED_WORK) {
			throw new CaseException(CASE_EXCEPTION_CODE_7, "the request has not yet been completed, please try again in a while");
		}

		// если запрос не смог выполниться успешно
		if ($status == Domain_Request_Entity_Request::STATUS_FAILED) {
			throw new CaseException((int) $result_data["error_code"], $result_data["message"]);
		}

		return $this->ok($result_data);
	}
}