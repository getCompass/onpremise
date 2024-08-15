<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Класс для проверки капчи
 */
abstract class Type_Captcha_Main {

	public const NAME = "";

	protected const _COMPASS_APP = "compass";
	protected const _COMTEAM_APP = "comteam";

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

		$is_mock = getConfig("CAPTCHA_PROVIDER_LIST") === [] && ServerProvider::isTest();

		return match ($recaptcha_special_header) {
			Type_Captcha_EnterpriseGoogle::NAME => $is_mock ? new Type_Captcha_EnterpriseGoogleMock() : new Type_Captcha_EnterpriseGoogle(),
			Type_Captcha_YandexCloud::NAME => $is_mock ? new Type_Captcha_YandexCloudMock() : new Type_Captcha_YandexCloud(),
			default => $is_mock ? new Type_Captcha_GoogleMock() : new Type_Captcha_Google(),
		};
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

		// если настройка говорит, что капча выключена
		if (!Domain_User_Entity_Auth_Config::isCaptchaEnabled()) {
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
	 * Получить публчиные данные капчи.
	 */
	public static function getProviderPublicCaptchaData(string $platform = ""):array {

		$captcha_provider_config = getConfig("CAPTCHA_PROVIDER_LIST");

		$user_agent     = mb_strtolower(getUa());
		$app_field_name = inHtml($user_agent, "compass") || ServerProvider::isOnPremise() ? self::_COMPASS_APP : self::_COMTEAM_APP;

		$provider_list = [];
		foreach ($captcha_provider_config as $provider => $provider_data) {

			$client_key                                    = match ($platform) {
				Type_Api_Platform::PLATFORM_ANDROID => inHtml($user_agent, "huawei")
					? $provider_data[$app_field_name]["client_keys"]["huawei_key"] ?? ""
					: $provider_data[$app_field_name]["client_keys"]["android_key"] ?? "",
				Type_Api_Platform::PLATFORM_IOS => $provider_data[$app_field_name]["client_keys"]["ios_key"] ?? "",
				Type_Api_Platform::PLATFORM_ELECTRON => $provider_data[$app_field_name]["client_keys"]["electron_key"] ?? "",
				default => $provider_data["compass"]["client_keys"]["default"] ?? "",
			};

			// если сервер онпрема и полученный ключ пустой, то пробуем достать дефолт клиентский ключ
			// (кейс, когда пользователь на своём onpremise-сервере заполнил в конфиге только дефолт клиентский ключ)
			if (ServerProvider::isOnPremise() && mb_strlen($client_key) < 1) {
				$client_key = $provider_data["compass"]["client_keys"]["default"] ?? "";
			}

			$provider_list[$provider]["client_public_key"] = $client_key;
		}

		return [
			"provider_list" => $provider_list,
		];
	}

	/**
	 * Получить конфиг провайдера капчи
	 * @return array
	 */
	protected function _getConfig():array {

		return getConfig("CAPTCHA_PROVIDER_LIST")[static::NAME] ?? [];
	}

	/**
	 * получить название приложения
	 */
	protected function _getAppName(string|false $user_agent = false):string {

		if ($user_agent === false) {
			$user_agent = mb_strtolower(getUa());
		}

		return inHtml($user_agent, "compass") || ServerProvider::isOnPremise() ? self::_COMPASS_APP : self::_COMTEAM_APP;
	}
}