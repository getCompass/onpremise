<?php

namespace Compass\Pivot;

/**
 * Класс для работы с API-шлюзом провайдера sms-agent.ru
 */
class Gateway_Sms_Provider_Vonage extends Gateway_Sms_Provider_Abstract {

	public const ID = "vonage_alphanumeric_v1";

	/**
	 * Отправить смс сообщение на номер с переданным текстом сообщения
	 *
	 * @param string $phone_number Номер телефона получатели
	 * @param string $text         Отправляемый текст сообщения
	 *
	 * @return Struct_Gateway_Sms_Provider_Response sms_id сообщения в контексте провайдера
	 * @throws \cs_CurlError
	 * @throws cs_SmsFailedRequestToProvider|\parseException
	 */
	public static function sendSms(string $phone_number, string $text):Struct_Gateway_Sms_Provider_Response {

		$response_struct = self::_makeRequest("sms/json", [
			"to"   => ltrim($phone_number, "+"),
			"text" => $text,
			"type" => "unicode",
		]);

		if ($response_struct->http_status_code != 200) {
			throw new cs_SmsFailedRequestToProvider($response_struct);
		}
		if ($response_struct->body["messages"][0]["status"] != 0) {
			throw new cs_SmsFailedRequestToProvider($response_struct);
		}
		return $response_struct;
	}

	/**
	 * VONAGE не умеет в status поэтому тут так
	 * Получить информацию об отправленном ранее сообщении
	 *
	 * @param string $sms_id Идентификатор сообщения
	 *
	 * @return Struct_Gateway_Sms_Provider_Response Ответ от провайдера
	 */
	public static function getSmsStatus(string $sms_id):Struct_Gateway_Sms_Provider_Response {

		return new Struct_Gateway_Sms_Provider_Response(200, timeMs(), []);
	}

	/**
	 * Получить значение остатка средства на балансе провайдера в валюте самого провайдера
	 *
	 * @return Struct_Gateway_Sms_Provider_Response
	 * @throws \cs_CurlError
	 * @throws cs_SmsFailedRequestToProvider|\parseException
	 */
	public static function getBalance():Struct_Gateway_Sms_Provider_Response {

		$response_struct = self::_makeRequest("account/get-balance", []);

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

		return $response->body["messages"][0]["message-id"];
	}

	/**
	 * Получить статус отправленного сообщения из тела ответа
	 *
	 * @param Struct_Gateway_Sms_Provider_Response $response
	 *
	 * @return int
	 */
	public static function getSmsStatusFromResponse(Struct_Gateway_Sms_Provider_Response $response):int {

		return self::STATUS_DELIVERED;
	}

	/**
	 * Получить временную метку, когда сообщение было отправлено провайдером оператору
	 *
	 * @param Struct_Gateway_Sms_Provider_Response $response
	 *
	 * @return int
	 */
	public static function getSmsSentAtFromResponse(Struct_Gateway_Sms_Provider_Response $response):int {

		return 0;
	}

	/**
	 * Получить целочисленного значение баланса из тела ответа
	 *
	 * @param Struct_Gateway_Sms_Provider_Response $response
	 *
	 * @return int
	 */
	public static function getBalanceValueFromResponse(Struct_Gateway_Sms_Provider_Response $response):int {

		return (int) $response->body["value"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * делаем запрос на api
	 *
	 * @param string $action
	 * @param array  $ar_post
	 *
	 * @return Struct_Gateway_Sms_Provider_Response
	 * @throws \cs_CurlError|\parseException
	 */
	protected static function _makeRequest(string $action, array $ar_post):Struct_Gateway_Sms_Provider_Response {

		if (isTestServer()) {
			return Gateway_Sms_Provider_Mock_Vonage::mockRequest($action, $ar_post);
		}

		// подготавливаем url запроса
		$url  = self::_prepareRequestUrl($action, $ar_post);
		$curl = new \Curl();
		$curl->setTimeout(self::PROVIDER_TIMEOUT);

		// приколы вонажа новые
		if (mb_strlen(VONAGE_PROXY) > 0) {
			$curl->setOpt(CURLOPT_PROXY, VONAGE_PROXY);
		}

		$response         = $curl->get($url);
		$http_status_code = $curl->getResponseCode();
		return new Struct_Gateway_Sms_Provider_Response($http_status_code, timeMs(), fromJson($response));
	}

	/**
	 * Сформировать URL-encoded строку запроса
	 *
	 * @param string $act
	 * @param array  $request_parameters
	 *
	 * @return string
	 * @throws \parseException
	 */
	protected static function _prepareRequestUrl(string $act, array $request_parameters):string {

		// получаем креды
		$credentials = self::_getConfig()["credential"];

		$required_parameters = [
			"api_key"    => $credentials["api_key"],
			"api_secret" => $credentials["api_secret"],
			"from"       => $credentials["from"],
		];

		return "{$credentials["gateway_url"]}$act?" . http_build_query(array_merge($required_parameters, $request_parameters));
	}

}