<?php

namespace Compass\Pivot;

/**
 * Класс для работы с API-шлюзом провайдера sms-agent.ru
 */
class Gateway_Sms_Provider_SmsAgent extends Gateway_Sms_Provider_Abstract {

	public const ID = "sms_agent_alphanumeric_v1";

	/**
	 * Отправить смс сообщение на номер с переданным текстом сообщения
	 *
	 * @param string $phone_number Номер телефона получатели
	 * @param string $text         Отправляемый текст сообщения
	 *
	 * @return Struct_Gateway_Sms_Provider_Response sms_id сообщения в контексте провайдера
	 *
	 * @throws \cs_CurlError
	 * @throws cs_SmsFailedRequestToProvider
	 */
	public static function sendSms(string $phone_number, string $text):Struct_Gateway_Sms_Provider_Response {

		$response_struct = self::_makeRequest("send", [
			"to"   => ltrim($phone_number, "+"),
			"text" => $text,
		]);

		if ($response_struct->http_status_code != 200) {

			if (isTestServer()) {
				Type_System_Admin::log("sms_send", ["Не отправили", $response_struct]);
			}
			throw new cs_SmsFailedRequestToProvider($response_struct);
		}

		return $response_struct;
	}

	/**
	 * Получить информацию об отправленном ранее сообщении
	 *
	 * @param string $sms_id Идентификатор сообщения
	 *
	 * @return Struct_Gateway_Sms_Provider_Response Ответ от провайдера
	 *
	 * @throws \cs_CurlError
	 * @throws cs_SmsFailedRequestToProvider
	 */
	public static function getSmsStatus(string $sms_id):Struct_Gateway_Sms_Provider_Response {

		$response_struct = self::_makeRequest("status", [
			"id" => $sms_id,
		]);

		if ($response_struct->http_status_code != 200) {
			throw new cs_SmsFailedRequestToProvider($response_struct);
		}

		return $response_struct;
	}

	/**
	 * Получить значение остатка средства на балансе провайдера в валюте самого провайдера
	 *
	 * @throws \cs_CurlError
	 * @throws cs_SmsFailedRequestToProvider
	 */
	public static function getBalance():Struct_Gateway_Sms_Provider_Response {

		$response_struct = self::_makeRequest("balans", []);

		if ($response_struct->http_status_code != 200) {
			throw new cs_SmsFailedRequestToProvider($response_struct);
		}

		return $response_struct;
	}

	/**
	 * Получить sms_id отправленного сообщения из тела ответа
	 *
	 */
	public static function getSmsIdFromResponse(Struct_Gateway_Sms_Provider_Response $response):string {

		return $response->body;
	}

	/**
	 * Получить статус отправленного сообщения из тела ответа
	 * @long
	 */
	public static function getSmsStatusFromResponse(Struct_Gateway_Sms_Provider_Response $response):int {

		$status = $response->body;
		switch ((int) $status) {

			case 0:
			case 1:

				return self::STATUS_IN_PROGRESS;

			case 2:

				return self::STATUS_DELIVERED;

			case 3:
			case 4:
			case 5:
			case 6:

				return self::STATUS_NOT_DELIVERED;

			default:

				Type_System_Admin::log(__METHOD__, "provider return unexpected status: {$status}");
				return self::STATUS_NOT_DELIVERED;
		}
	}

	/**
	 * Получить временную метку, когда сообщение было отправлено провайдером оператору
	 *
	 */
	public static function getSmsSentAtFromResponse(Struct_Gateway_Sms_Provider_Response $response):int {

		return 0;
	}

	/**
	 * Получить целочисленного значение баланса из тела ответа
	 *
	 */
	public static function getBalanceValueFromResponse(Struct_Gateway_Sms_Provider_Response $response):int {

		return (int) $response->body;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * делаем запрос на api
	 *
	 * @throws \cs_CurlError
	 */
	protected static function _makeRequest(string $action, array $ar_post):Struct_Gateway_Sms_Provider_Response {

		// если это тестовое окружение, то мокаем
		if (isTestServer()) {
			return Gateway_Sms_Provider_Mock_SmsAgent::mockRequest($action, $ar_post);
		}

		// подготавливаем url запроса
		$url = self::_prepareRequestUrl($action, $ar_post);

		$curl = new \Curl();
		$curl->setTimeout(self::PROVIDER_TIMEOUT);

		$response         = $curl->get($url);
		$http_status_code = $curl->getResponseCode();

		return new Struct_Gateway_Sms_Provider_Response($http_status_code, timeMs(), $response);
	}

	/**
	 * Сформировать URL-encoded строку запроса
	 *
	 * @throws \parseException
	 */
	protected static function _prepareRequestUrl(string $act, array $request_parameters):string {

		// получаем креды
		$credentials = self::_getConfig()["credential"];

		$required_parameters = [
			"login" => $credentials["login"],
			"pass"  => $credentials["password"],
			"act"   => $act,
			"from"  => $credentials["from"],
		];

		return "{$credentials["gateway_url"]}?" . http_build_query(array_merge($required_parameters, $request_parameters));
	}
}