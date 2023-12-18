<?php

namespace Compass\Pivot;

/**
 * Класс для работы с API-шлюзом провайдера twilio
 */
class Gateway_Sms_Provider_Twilio extends Gateway_Sms_Provider_Abstract {

	public const ID = "twilio_alphanumeric_v1";

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
			"To"   => $phone_number,
			"Body" => $text,
		];

		$response_struct = self::_makeRequest("Messages.json", true, $payload);

		if ($response_struct->http_status_code != 201) {
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
	 * @throws cs_SmsFailedRequestToProvider|\parseException
	 */
	public static function getSmsStatus(string $sms_id):Struct_Gateway_Sms_Provider_Response {

		$response_struct = self::_makeRequest("Messages/{$sms_id}.json", false);

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

		$response_struct = self::_makeRequest("Balance.json", false);

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

		return fromJson($response->body)["sid"];
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

			case "queued":
			case "scheduled":
			case "sent":
			case "sending":
			case "received":
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

		return strtotime(fromJson($response->body)["date_created"]);
	}

	/**
	 * Получить целочисленного значение баланса из тела ответа
	 *
	 * @param Struct_Gateway_Sms_Provider_Response $response
	 *
	 * @return int
	 */
	public static function getBalanceValueFromResponse(Struct_Gateway_Sms_Provider_Response $response):int {

		return (int) fromJson($response->body)["balance"];
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
	protected static function _makeRequest(string $action, bool $is_post = true, array $ar_post = []):Struct_Gateway_Sms_Provider_Response {

		// если это тестовое окружение, то мокаем
		if (isTestServer()) {
			return Gateway_Sms_Provider_Mock_Twilio::mockRequest($action, $ar_post);
		}

		// получаем креды
		$credentials = self::_getConfig()["credential"];

		// подготавливаем url запроса
		$ar_post["From"] = $credentials["from"];
		$url             = self::_prepareRequestUrl($credentials["gateway_url"], $action, $credentials["account_sid"]);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::PROVIDER_TIMEOUT);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_USERPWD, $credentials["account_sid"] . ":" . $credentials["account_auth_token"]);

		if ($is_post) {

			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($ar_post));
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
	protected static function _prepareRequestUrl(string $gateway_url, string $method, string $account_sid):string {

		return "$gateway_url/Accounts/" . $account_sid . "/" . $method;
	}

}