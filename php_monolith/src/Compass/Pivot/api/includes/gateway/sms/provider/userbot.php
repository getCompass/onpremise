<?php

namespace Compass\Pivot;

/**
 * Класс для работы с отправки текста смс через бота
 */
class Gateway_Sms_Provider_Userbot {

	public const ID = "userbot_v1";

	/**
	 * Отправить сообщение с переданным текстом сообщения
	 *
	 * @return Struct_Gateway_Sms_Provider_Response
	 * @throws \cs_CurlError
	 * @throws cs_PhoneNumberNotFound
	 * @throws cs_SmsFailedRequestToProvider
	 */
	public static function sendSms(string $phone_number, string $text):Struct_Gateway_Sms_Provider_Response {

		// получаем user_id по номеру телефона
		$user_id = Domain_User_Entity_Phone::getUserIdByPhone($phone_number);

		$response_struct = self::_doCall($user_id, $text);

		if ($response_struct->http_status_code != 200) {

			if (isTestServer() || isStageServer()) {
				Type_System_Admin::log("sms_send_userbot", ["Не отправили", $response_struct]);
			}
			throw new cs_SmsFailedRequestToProvider($response_struct);
		}

		return $response_struct;
	}

	/**
	 * Получить информацию об отправленном ранее сообщении
	 *
	 * @return Struct_Gateway_Sms_Provider_Response
	 */
	public static function getSmsStatus():Struct_Gateway_Sms_Provider_Response {

		return new Struct_Gateway_Sms_Provider_Response(200, timeMs(), []);
	}

	/**
	 * Получить sms_id отправленного сообщения из тела ответа
	 */
	public static function getSmsIdFromResponse(Struct_Gateway_Sms_Provider_Response $response):string {

		return $response->body;
	}

	/**
	 * Получить статус отправленного сообщения из тела ответа
	 */
	public static function getSmsStatusFromResponse():int {

		return Gateway_Sms_Provider_Abstract::STATUS_DELIVERED;
	}

	/**
	 * Получить временную метку, когда сообщение было отправлено смс
	 *
	 */
	public static function getSmsSentAtFromResponse():int {

		return time();
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * выполняем запрос
	 *
	 * @return Struct_Gateway_Sms_Provider_Response
	 * @throws \cs_CurlError
	 */
	protected static function _doCall(int $user_id, string $text):Struct_Gateway_Sms_Provider_Response {

		// подготавливаем url запроса
		$url = self::_getWebhookUrl();

		$params = [
			"user_id" => $user_id,
			"text"    => $text,
		];

		$curl = new \Curl();
		$curl->setTimeout(Gateway_Sms_Provider_Abstract::PROVIDER_TIMEOUT);

		$response = $curl->post($url, $params);

		$http_status_code = $curl->getResponseCode();

		return new Struct_Gateway_Sms_Provider_Response($http_status_code, timeMs(), $response);
	}

	/**
	 * получаем ссылку для запроса
	 */
	protected static function _getWebhookUrl():string {

		return PUBLIC_ENTRYPOINT_STAGE . "/webhook/stage/authorization/user";
	}
}