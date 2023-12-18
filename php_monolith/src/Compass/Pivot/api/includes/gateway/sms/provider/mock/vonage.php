<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для мокинга работы с API-шлюзом провайдера twilio
 */
class Gateway_Sms_Provider_Mock_Vonage extends Gateway_Sms_Provider_Mock_Default {

	/**
	 * мокаем отправку реквеста
	 *
	 * @return Struct_Gateway_Sms_Provider_Response
	 * @throws cs_SmsFailedRequestToProvider
	 * @throws ParseFatalException
	 * @throws \cs_CurlError
	 */
	public static function mockRequest(string $action, array $ar_post):Struct_Gateway_Sms_Provider_Response {

		return match ($action) {
			"sms/json"            => self::_mockSendMessage($ar_post["to"], $ar_post["text"]),
			"account/get-balance" => function() {

				return new Struct_Gateway_Sms_Provider_Response(200, timeMs(), ["value" => 5000]);
			},
			default               => throw new ParseFatalException("unexpected behaviour"),
		};
	}

	/**
	 * мокаем отправку смс сообщения
	 *
	 * @return Struct_Gateway_Sms_Provider_Response
	 * @throws \cs_CurlError
	 * @throws cs_SmsFailedRequestToProvider
	 */
	protected static function _mockSendMessage(string $phone_number, string $text):Struct_Gateway_Sms_Provider_Response {

		// отправляем сообщение
		self::_sendMessage($phone_number, $text);

		return new Struct_Gateway_Sms_Provider_Response(200, timeMs(), ["messages" => [["status" => 0, "message-id" => generateUUID()]]]);
	}

}