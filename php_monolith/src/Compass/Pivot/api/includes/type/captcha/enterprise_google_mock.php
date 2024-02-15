<?php

namespace Compass\Pivot;

/**
 * Мок проверки гугл капчи Enterprise
 *
 * Class Type_Captcha_EnterpriseGoogleMock
 */
class Type_Captcha_EnterpriseGoogleMock extends Type_Captcha_EnterpriseGoogle {

	/** @var string Ключ мока */
	protected const MOCK_KEY = "enterprise_captcha";

	/**
	 * проверить, что пользователь прошел проверку
	 *
	 */
	public function check(string $captcha, string $platform, string $user_action = ""):bool {

		$ar_post = $this->_getRequestData($captcha, $platform, $user_action);

		try {
			$response = $this->_makeRequest($this->_getRequestUrl(), $ar_post);
		} catch (\cs_CurlError) {
			return false;
		}

		// если запрос не вернул ok
		return $response["success"] ?? false;
	}

	/**
	 * Получить публчиный ключ для каптчи.
	 */
	public function getPublicCaptchaKey(string $platform = ""):string {

		$captcha_provider_config = $this->_getConfig();

		return $captcha_provider_config["compass"]["client_keys"]["default"] ?? "";
	}

	/**
	 * сделать запрос
	 *
	 * @throws \cs_CurlError
	 */
	protected function _makeRequest(string $url, array $ar_post):array {

		return fromJson(Type_Mock_Service::makeRequest(self::MOCK_KEY, $ar_post));
	}

	/**
	 * установить успешный ответ при проверке капчи
	 */
	public static function setSuccessMockResponse():void {

		Type_Mock_Service::setExpectResponseFromMock(self::MOCK_KEY, [
			"success"      => true,
			"challenge_ts" => date("Y-m-d'T'H:i:s"),
			"hostname"     => SERVER_NAME,
		]);
	}

	/**
	 * установить отрицательный ответ при проверке капчи
	 */
	public static function setFailedMockResponse():void {

		Type_Mock_Service::setExpectResponseFromMock(self::MOCK_KEY, [
			"success"      => false,
			"challenge_ts" => date("Y-m-d'T'H:i:s"),
			"hostname"     => SERVER_NAME,
		]);
	}
}