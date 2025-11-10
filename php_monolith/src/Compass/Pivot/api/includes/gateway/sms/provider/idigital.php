<?php

namespace Compass\Pivot;

/**
 * Класс для работы с API-шлюзом провайдера direct.i-dgtl.ru
 */
class Gateway_Sms_Provider_Idigital extends Gateway_Sms_Provider_Abstract {

	public const ID = "idigital_alphanumeric_v1";

	/**
	 * Отправить смс сообщение на номер с переданным текстом сообщения
	 *
	 * @param string $phone_number Номер телефона получатели
	 * @param string $text         Отправляемый текст сообщения
	 *
	 * @return Struct_Gateway_Sms_Provider_Response sms_id сообщения в контексте провайдера
	 * @throws cs_SmsFailedRequestToProvider|\parseException
	 */
	public static function sendSms(string $phone_number, string $text):Struct_Gateway_Sms_Provider_Response {

		$payload = [
			"channelType" => "SMS",
			"destination" => $phone_number,
			"content"     => $text,
		];

		$headers = [
			"Content-Type" => "application/json",
		];

		$response_struct = self::_makeRequest("message", true, $payload, $headers);

		if ($response_struct->http_status_code != 200) {
			throw new cs_SmsFailedRequestToProvider($response_struct);
		}

		return $response_struct;
	}

	/**
	 * Получить sms_id отправленного сообщения из тела ответа
	 *
	 * @param Struct_Gateway_Sms_Provider_Response $response
	 *
	 * @return string
	 */
	public static function getSmsIdFromResponse(Struct_Gateway_Sms_Provider_Response $response):string {

		$response = fromJson($response->body);

		return current($response["items"])["messageUuid"] ?? "";
	}

	/**
	 * Получить информацию об отправленном ранее сообщении
	 *
	 * @param string $sms_id Идентификатор сообщения
	 *
	 * @return Struct_Gateway_Sms_Provider_Response Ответ от провайдера
	 * @throws cs_SmsFailedRequestToProvider|\parseException
	 */
	public static function getSmsStatus(string $sms_id):Struct_Gateway_Sms_Provider_Response {

		$response_struct = self::_makeRequest("message/{$sms_id}", false);

		if ($response_struct->http_status_code != 200) {
			throw new cs_SmsFailedRequestToProvider($response_struct);
		}

		return $response_struct;
	}

	/**
	 * Получить значение остатка средства на балансе провайдера в валюте самого провайдера
	 *
	 * @return Struct_Gateway_Sms_Provider_Response
	 * @throws cs_SmsFailedRequestToProvider|\parseException
	 */
	public static function getBalance():Struct_Gateway_Sms_Provider_Response {

		$response_struct = self::_makeRequest("balance", false);

		if ($response_struct->http_status_code != 200) {
			throw new cs_SmsFailedRequestToProvider($response_struct);
		}

		return $response_struct;
	}

	/**
	 * Получить статус отправленного сообщения из тела ответа
	 *
	 * @param Struct_Gateway_Sms_Provider_Response $response
	 *
	 * @return int
	 */
	public static function getSmsStatusFromResponse(Struct_Gateway_Sms_Provider_Response $response):int {

		$status = fromJson($response->body)["status"];
		switch ((string) mb_strtolower($status)) {

			case "unsent":
			case "sent":
			case "sending":
				return self::STATUS_IN_PROGRESS;

			case "delivered":
				return self::STATUS_DELIVERED;

			default:

				Type_System_Admin::log(__METHOD__, "provider return unexpected status: {$status}");
				return self::STATUS_NOT_DELIVERED;
		}
	}

	/**
	 * Получить временную метку, когда сообщение было отправлено провайдером оператору
	 *
	 * @param Struct_Gateway_Sms_Provider_Response $response
	 *
	 * @return int
	 */
	public static function getSmsSentAtFromResponse(Struct_Gateway_Sms_Provider_Response $response):int {

		return strtotime(fromJson($response->body)["sentTime"]);
	}

	/**
	 * Получить целочисленного значение баланса из тела ответа
	 *
	 * @param Struct_Gateway_Sms_Provider_Response $response
	 *
	 * @return int
	 */
	public static function getBalanceValueFromResponse(Struct_Gateway_Sms_Provider_Response $response):int {

		return (int) current(fromJson($response->body));
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * делаем запрос на api
	 *
	 * @param string $action
	 * @param array  $ar_post
	 * @param bool   $is_post
	 *
	 * @return Struct_Gateway_Sms_Provider_Response
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \parseException
	 */
	protected static function _makeRequest(string $action, bool $is_post = true, array $ar_post = [], array $headers = []):Struct_Gateway_Sms_Provider_Response {

		// если это тестовое окружение, то мокаем
		if (isTestServer()) {
			return Gateway_Sms_Provider_Mock_Idigital::mockRequest($action, $ar_post);
		}

		// получаем креды
		$credentials = self::_getConfig()["credential"];

		$authorization_headers = [
			"Authorization" => "Basic " . $credentials["api_header_key"],
		];
		$headers               = array_merge($headers, $authorization_headers);

		if ($action == "message" && !isset($ar_post["senderName"])) {
			$ar_post["senderName"] = $credentials["from"];
		}

		// подготавливаем url запроса
		$url = self::_prepareRequestUrl($credentials["gateway_url"], $action);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::PROVIDER_TIMEOUT);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, self::_doFormatHeaders($headers));

		if ($is_post) {

			$ar_post = \json_encode($ar_post);

			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $ar_post);
		}
		$response = curl_exec($ch);

		return new Struct_Gateway_Sms_Provider_Response(formatInt(curl_getinfo($ch, CURLINFO_HTTP_CODE)), timeMs(), $response);
	}

	/**
	 * Сформировать URL-encoded строку запроса
	 *
	 * @param string $gateway_url
	 * @param string $method
	 * @param string $account_sid
	 *
	 * @return string
	 */
	protected static function _prepareRequestUrl(string $gateway_url, string $method):string {

		if (!str_ends_with($gateway_url, "/")) {
			$gateway_url .= "/";
		}

		return $gateway_url . $method;
	}

	/**
	 * получаем форматированные заголовки
	 */
	protected static function _doFormatHeaders(array $headers):array {

		$output = [];
		foreach ($headers as $key => $value) {
			$output[] = $key . ": " . $value;
		}

		return $output;
	}
}