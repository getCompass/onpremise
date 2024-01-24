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
	abstract public function check(string $captcha, string $platform):bool;

	/**
	 * Получить публчиный ключ для каптчи.
	 */
	abstract public function getPublicCaptchaKey(string $platform = ""):string;

	/**
	 * Получаем необходимый объект проверки капчи
	 *
	 */
	public static function init():Type_Captcha_Main {

		if (getConfig("CAPTCHA_PROVIDER_LIST") === [] && ServerProvider::isTest()) {
			return new Type_Captcha_GoogleMock();
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
	public static function assertCaptcha(string|false $grecaptcha_response):void {

		// если нет конфига - то и делать здесь нечего
		if (getConfig("CAPTCHA_PROVIDER_LIST") === [] && !ServerProvider::isTest()) {
			return;
		}

		if ($grecaptcha_response === false) {
			throw new cs_RecaptchaIsRequired();
		}

		$platform = Type_Api_Platform::getPlatform(getUa());

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