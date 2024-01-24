<?php

namespace Compass\Company;

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

		return fromJson($mocked_response["response"]["response"])["text"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * возвращает ключ, по которому мокается текст отправляемого сообщения
	 */
	protected static function _getKeyForMockedText(string $phone_number):string {

		return sprintf("sms_text_%s", ltrim($phone_number, "+"));
	}
}