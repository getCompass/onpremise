<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Класс для проверки капчи
 */
abstract class Type_Captcha_Main {

	public const NAME = "";

	/**
	 * Проверка капчи
	 *
	 */
	abstract public function check(string $captcha, string $platform, string $user_action = ""):bool;

	/**
	 * Получить публчиный ключ для каптчи.
	 */
	abstract public function getPublicCaptchaKey(string $platform = ""):string;

	/**
	 * Получаем необходимый объект проверки капчи
	 *
	 */
	public static function init():Type_Captcha_Main {

		$recaptcha_special_header = getHeader("HTTP_X_COMPASS_CAPTCHA_METHOD");
		$recaptcha_special_header = mb_strtolower($recaptcha_special_header);

		if (getConfig("CAPTCHA_PROVIDER_LIST") === [] && ServerProvider::isTest()) {
			return $recaptcha_special_header == Type_Captcha_EnterpriseGoogle::NAME ? new Type_Captcha_EnterpriseGoogleMock() : new Type_Captcha_GoogleMock();
		}

		if ($recaptcha_special_header == Type_Captcha_EnterpriseGoogle::NAME) {
			return new Type_Captcha_EnterpriseGoogle();
		}

		return new Type_Captcha_Google();
	}

	/**
	 * Проверяем, есть ли решение капчи и верное ли оно
	 *
	 * @throws cs_PlatformNotFound
	 * @throws cs_RecaptchaIsRequired
	 * @throws cs_WrongRecaptcha
	 */
	public static function assertCaptcha(string|false $grecaptcha_response, bool $is_from_web = false):void {

		// если нет конфига - то и делать здесь нечего
		if (getConfig("CAPTCHA_PROVIDER_LIST") === [] && !ServerProvider::isTest()) {
			return;
		}

		if ($grecaptcha_response === false) {
			throw new cs_RecaptchaIsRequired();
		}

		$platform = Type_Api_Platform::getPlatform(getUa());
		if ($is_from_web) {
			$platform = Type_Api_Platform::PLATFORM_OTHER;
		}

		// проверяем капчу
		if (self::init()->check($grecaptcha_response, $platform) === false) {
			throw new cs_WrongRecaptcha();
		}
	}

	/**
	 * Получить конфиг провайдера капчи
	 * @return array
	 */
	protected function _getConfig():array {

		return getConfig("CAPTCHA_PROVIDER_LIST")[static::NAME] ?? [];
	}
}