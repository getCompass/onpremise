<?php

namespace Compass\Pivot;

/**
 * Проверка гугл капчи
 *
 * Class Type_Captcha_Google
 */
class Type_Captcha_Google extends Type_Captcha_Main {

	public const NAME = "google";

	/** @var string URL API recaptcha */
	protected const URL = "https://www.google.com/recaptcha/api/siteverify";

	/** @var string заголовок форсированного использования SECRET_CAPTCHA_SITE */
	protected const _USE_SECRET_CAPTCHA_SITE = "USE_SECRET_CAPTCHA_SITE";

	/** @var string[] список доступных special штук */
	protected const _ALLOWED_SPECIAL = [
		"USE_SECRET_CAPTCHA_SITE",
	];

	/**
	 * проверить, что пользователь прошел проверку
	 *
	 */
	public function check(string $captcha, string $platform, string $user_action = ""):bool {

		$captcha_provider_config = $this->_getConfig();

		// если конфига нет - завершаем выполнение
		if ($captcha_provider_config === []) {
			return true;
		}

		$recaptcha_special = $this->_getRecaptchaSpecial();
		$ar_post           = $this->_getRequestData($captcha, $platform, $captcha_provider_config, $recaptcha_special);

		try {
			$response = $this->_makeRequest($ar_post);
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
	protected function _makeRequest(array $ar_post):array {

		// инстанс курла
		$curl = new \Curl();
		return fromJson($curl->post(self::URL, $ar_post));
	}

	/**
	 * Сфоримровать массив для запроса
	 */
	protected function _getRequestData(string $captcha, string $platform, array $captcha_provider_config, string $recaptcha_special = ""):array {

		// массив для гугла
		$ar_post = [
			"response" => $captcha,
			"remoteip" => getIp(),
		];

		if ($recaptcha_special === static::_USE_SECRET_CAPTCHA_SITE) {

			$ar_post["secret"] = $captcha_provider_config["compass"]["server_key"] ?? "";
			return $ar_post;
		}

		$ar_post["secret"] = self::_getServerCaptchaKey($platform);

		return $ar_post;
	}

	/**
	 * Получить ключ для отправки запроса
	 */
	protected function _getServerCaptchaKey(string $platform = ""):string {

		$captcha_provider_config = $this->_getConfig();

		switch ($platform) {

			case Type_Api_Platform::PLATFORM_ANDROID:

				$user_agent = mb_strtolower(getUa());
				if (inHtml($user_agent, "huawei")) {
					return $captcha_provider_config["compass"]["server_key"] ?? "";
				} else {

					if (inHtml($user_agent, "compass")) {
						return $captcha_provider_config["compass"]["android_server_key"] ?? "";
					} else {
						return $captcha_provider_config["comteam"]["android_server_key"] ?? "";
					}
				}

			default:
				return $captcha_provider_config["compass"]["server_key"] ?? "";
		}
	}

	/**
	 * Формирует special данные для проверки каптчи.
	 *
	 */
	protected function _getRecaptchaSpecial():string {

		$recaptcha_special_header = getHeader("HTTP_X_COMPASS_GRECAPTCHA_SPECIAL");
		$recaptcha_special_header = mb_strtoupper($recaptcha_special_header);

		return in_array($recaptcha_special_header, static::_ALLOWED_SPECIAL)
			? $recaptcha_special_header
			: "";
	}
}