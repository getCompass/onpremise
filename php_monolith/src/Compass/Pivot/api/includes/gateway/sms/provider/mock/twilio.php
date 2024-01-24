<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для мокинга работы с API-шлюзом провайдера twilio
 */
class Gateway_Sms_Provider_Mock_Twilio extends Gateway_Sms_Provider_Mock_Default {

	/**
	 * мокаем отправку реквеста
	 *
	 * @param string $action
	 * @param array  $ar_post
	 *
	 * @return Struct_Gateway_Sms_Provider_Response
	 * @throws cs_SmsFailedRequestToProvider
	 * @throws ParseFatalException
	 * @throws \cs_CurlError
	 */
	public static function mockRequest(string $action, array $ar_post):Struct_Gateway_Sms_Provider_Response {

		// если это отправка сообщения
		if ($action === "Messages.json") {
			return self::_mockSendMessage($ar_post["To"], $ar_post["Body"]);
		}

		// если это получение статуса смс
		if (inHtml($action, "Messages/")) {

			$sms_id = self::_getSmsIdFromAction($action);
			return self::_mockGetSmsStatus($sms_id);
		}

		// если это получение баланса
		if ($action === "Balance.json") {

			// балансе всегда 5000
			return new Struct_Gateway_Sms_Provider_Response(200, timeMs(), ["balance" => 5000]);
		}

		throw new ParseFatalException(__METHOD__ . ": unexpected action");
	}

	/**
	 * мокаем отправку смс сообщения
	 *
	 * @return Struct_Gateway_Sms_Provider_Response
	 * @throws \cs_CurlError
	 * @throws cs_SmsFailedRequestToProvider
	 */
	protected static function _mockSendMessage(string $phone_number, string $text):Struct_Gateway_Sms_Provider_Response {

		// генерируем случаный sms_id
		$sms_id = generateUUID();

		// отправляем сообщение
		self::_sendMessage($phone_number, $text);

		return new Struct_Gateway_Sms_Provider_Response(201, timeMs(), toJson(["sid" => $sms_id]));
	}

	/**
	 * получаем sms_id из запроса
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	protected static function _getSmsIdFromAction(string $action):string {

		if (!preg_match("/Messages\/(.+)\.json/", $action, $matches)) {
			throw new ParseFatalException("unexpected action");
		}

		return $matches[1];
	}

	/**
	 * мокаем получение статуса отправки смс
	 *
	 * @return Struct_Gateway_Sms_Provider_Response
	 * @throws \cs_CurlError
	 */
	protected static function _mockGetSmsStatus(string $sms_id):Struct_Gateway_Sms_Provider_Response {

		// статус отправки – по умолчанию delivered
		$default_response = new Struct_Gateway_Sms_Provider_Response(200, timeMs(), toJson(["status" => "delivered", "date_created" => date(DATE_FORMAT_FULL_S, time())]));

		// получаем мокнутый статус
		return Type_Sms_Mock::getSmsStatus($sms_id, $default_response);
	}
}