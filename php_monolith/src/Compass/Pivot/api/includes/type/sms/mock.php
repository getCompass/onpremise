<?php

namespace Compass\Pivot;

/**
 * Вспомогательный класс для мокинга и полноценный работы смс-провайдеров в тестовой среде
 */
class Type_Sms_Mock {

	/**
	 * мокаем текст сообщения
	 *
	 * @throws \cs_CurlError
	 */
	public static function saveText(string $phone_number, string $sms_text):void {

		Type_Mock_Service::makeRequest(self::_getKeyForMockedText($phone_number), ["text" => $sms_text]);
	}

	/**
	 * получаем замоканный текст сообщения
	 *
	 * @throws \cs_CurlError
	 * @throws cs_MockedDataIsNotFound
	 */
	public static function getText(string $phone_number):string {

		$key             = self::_getKeyForMockedText($phone_number);
		$mocked_response = self::_getMockedResponse($key);

		return fromJson($mocked_response["response"]["response"])["text"];
	}

	/**
	 * мокаем сатус отправки смс
	 *
	 * @throws \cs_CurlError
	 */
	public static function saveSmsStatus(string $sms_id, Struct_Gateway_Sms_Provider_Response $response):void {

		Type_Mock_Service::makeRequest(self::_getKeyForMockedStatus($sms_id), (array) $response);
	}

	/**
	 * получаем мокнутый статус отправки смс
	 *
	 * @return Struct_Gateway_Sms_Provider_Response
	 * @throws \cs_CurlError
	 */
	public static function getSmsStatus(string $sms_id, Struct_Gateway_Sms_Provider_Response $default_response):Struct_Gateway_Sms_Provider_Response {

		$key = self::_getKeyForMockedStatus($sms_id);

		try {
			$mocked_response = self::_getMockedResponse($key);
		} catch (cs_MockedDataIsNotFound) {
			return $default_response;
		}

		$response = fromJson($mocked_response["response"]["response"]);

		return new Struct_Gateway_Sms_Provider_Response(
			$response["http_status_code"],
			$response["request_send_at_ms"],
			$response["body"]
		);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получаем мокнутые данные
	 *
	 * @return array
	 * @throws \cs_CurlError
	 * @throws cs_MockedDataIsNotFound
	 */
	protected static function _getMockedResponse(string $key):array {

		$mocked_response = fromJson(Type_Mock_Service::getRequestByKey($key));

		if ($mocked_response["status"] == "error") {

			Type_System_Admin::log("do_resend_sms_cest", [
				"error"             => "cs_MockedDataIsNotFound",
				"key"               => $key,
				"all_mock_response" => Type_Mock_Service::getAllRequest(),
			]);
			throw new cs_MockedDataIsNotFound();
		}

		// подчищаем мокнутые данные
		Type_Mock_Service::resetRequest($key);

		return $mocked_response;
	}

	/**
	 * возвращает ключ, по которому мокается текст отправляемого сообщения
	 */
	protected static function _getKeyForMockedText(string $phone_number):string {

		return sprintf("sms_text_%s", ltrim($phone_number, "+"));
	}

	/**
	 * возвращает ключ, по которому мокается статус отправляемого сообщения
	 */
	protected static function _getKeyForMockedStatus(string $sms_id):string {

		return sprintf("sms_status_%s", $sms_id);
	}
}