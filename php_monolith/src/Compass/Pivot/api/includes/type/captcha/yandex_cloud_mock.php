<?php

namespace Compass\Pivot;

/**
 * Проверка yandex-капчи
 *
 * Class Type_Captcha_YandexCloudMock
 */
class Type_Captcha_YandexCloudMock extends Type_Captcha_YandexCloud {

	protected const _MOCK_KEY = "yandex_cloud";

	public const MOCK_SUCCESS_CAPTCHA_TOKEN = "mock_success_token"; // токен ответа капчи для успешного прохождения
	public const MOCK_FAILED_CAPTCHA_TOKEN  = "mock_failed_token";  // токен ответа капчи для фейла

	public const MOCK_CAPTCHA_TOKEN_LIST = [
		self::MOCK_SUCCESS_CAPTCHA_TOKEN,
		self::MOCK_FAILED_CAPTCHA_TOKEN,
	];

	/**
	 * проверить, что пользователь прошел проверку
	 *
	 */
	public function check(string $captcha, string $platform, string $user_action = ""):bool {

		$ar_post = $this->_getRequestData($captcha);

		try {
			$response = $this->_makeRequest(self::_URL, $ar_post);
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

		return fromJson(Type_Mock_Service::makeRequest(self::_MOCK_KEY, $ar_post));
	}

	/**
	 * установить успешный ответ при проверке капчи
	 */
	public static function setSuccessMockResponse():void {

		Type_Mock_Service::setExpectResponseFromMock(self::_MOCK_KEY, [
			"success"      => true,
			"challenge_ts" => date("Y-m-d'T'H:i:s"),
			"hostname"     => SERVER_NAME,
		]);
	}

	/**
	 * установить отрицательный ответ при проверке капчи
	 */
	public static function setFailedMockResponse():void {

		Type_Mock_Service::setExpectResponseFromMock(self::_MOCK_KEY, [
			"success"      => false,
			"challenge_ts" => date("Y-m-d'T'H:i:s"),
			"hostname"     => SERVER_NAME,
		]);
	}

	/**
	 * получаем мокнутые данные
	 *
	 * @return array
	 * @throws \cs_CurlError
	 */
	public static function getMockedResponse():array {

		$mocked_response = fromJson(Type_Mock_Service::getRequestByKey(self::_MOCK_KEY));

		// подчищаем мокнутые данные
		Type_Mock_Service::resetRequest(self::_MOCK_KEY);

		return $mocked_response;
	}
}