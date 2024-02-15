<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Проверка гугл капчи Enterprise версии
 *
 * Class Type_Captcha_EnterpriseGoogle
 */
class Type_Captcha_EnterpriseGoogle extends Type_Captcha_Main {

	public const NAME = "enterprise_google";

	/** @var string URL API recaptcha */
	protected const URL = "https://recaptchaenterprise.googleapis.com/v1/projects/{PROJECT_ID}/assessments?key={SERVER_PRIVATE_API_KEY}";

	protected const REQUEST_TIMEOUT = 20;

	/**
	 * проверить, что пользователь прошел проверку
	 *
	 */
	public function check(string $captcha, string $platform, string $user_action = "check_captcha"):bool {

		$captcha_provider_config = $this->_getConfig();

		// если конфига нет - завершаем выполнение
		if ($captcha_provider_config === []) {
			return true;
		}

		$ar_post = $this->_getRequestData($captcha, $platform, $user_action);

		try {
			$response = $this->_makeRequest($this->_getRequestUrl(), $ar_post);
		} catch (\cs_CurlError) {
			return false;
		}

		// если запрос не вернул ok
		return $response["tokenProperties"]["valid"] ?? false;
	}

	/**
	 * Сфоримровать массив для запроса
	 */
	protected function _getRequestData(string $captcha, string $platform, string $action):array {

		return [
			"event" => [
				"token"          => $captcha,
				"siteKey"        => self::getPublicCaptchaKey($platform),
				"expectedAction" => $action,
			],
		];
	}

	/**
	 * получить url для запроса
	 */
	protected function _getRequestUrl():string {

		$url = self::URL;

		$url = str_replace("{PROJECT_ID}", self::_getServerProjectId(), $url);
		$url = str_replace("{SERVER_PRIVATE_API_KEY}", self::_getServerCaptchaKey(), $url);

		return $url;
	}

	/**
	 * Получить id проекта для отправки запроса
	 */
	protected function _getServerProjectId():string {

		$captcha_provider_config = $this->_getConfig();

		$user_agent = mb_strtolower(getUa());
		if (inHtml($user_agent, "compass") || ServerProvider::isOnPremise()) {
			return $captcha_provider_config["compass"]["project_id"] ?? "";
		} else {
			return $captcha_provider_config["comteam"]["project_id"] ?? "";
		}
	}

	/**
	 * Получить приватный ключ для отправки запроса
	 */
	protected function _getServerCaptchaKey():string {

		$captcha_provider_config = $this->_getConfig();

		$user_agent = mb_strtolower(getUa());
		if (inHtml($user_agent, "compass") || ServerProvider::isOnPremise()) {
			return $captcha_provider_config["compass"]["server_key"] ?? "";
		} else {
			return $captcha_provider_config["comteam"]["server_key"] ?? "";
		}
	}

	/**
	 * сделать запрос
	 *
	 * @throws \cs_CurlError
	 */
	protected function _makeRequest(string $url, array $ar_post):array {

		$ar_post = \json_encode($ar_post);

		// инстанс курла
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::REQUEST_TIMEOUT);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $ar_post);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
			"Content-Type: application/json; charset=utf-8",
		], []));
		$response = curl_exec($ch);

		return fromJson($response);
	}

	/**
	 * Получить публчиный ключ для каптчи.
	 * @long switch..case
	 */
	public function getPublicCaptchaKey(string $platform = ""):string {

		$captcha_provider_config = $this->_getConfig();

		$user_agent = mb_strtolower(getUa());

		switch ($platform) {

			case Type_Api_Platform::PLATFORM_ANDROID:

				if (inHtml($user_agent, "huawei")) {

					if (inHtml($user_agent, "compass") || ServerProvider::isOnPremise()) {
						return $captcha_provider_config["compass"]["client_keys"]["huawei_key"] ?? "";
					} else {
						return $captcha_provider_config["comteam"]["client_keys"]["huawei_key"] ?? "";
					}
				} else {

					if (inHtml($user_agent, "compass") || ServerProvider::isOnPremise()) {
						return $captcha_provider_config["compass"]["client_keys"]["android_key"] ?? "";
					} else {
						return $captcha_provider_config["comteam"]["client_keys"]["android_key"] ?? "";
					}
				}

			case Type_Api_Platform::PLATFORM_IOS:

				if (inHtml($user_agent, "compass") || ServerProvider::isOnPremise()) {
					return $captcha_provider_config["compass"]["client_keys"]["ios_key"] ?? "";
				} else {
					return $captcha_provider_config["comteam"]["client_keys"]["ios_key"] ?? "";
				}

			case Type_Api_Platform::PLATFORM_ELECTRON:

				if (inHtml($user_agent, "compass") || ServerProvider::isOnPremise()) {
					return $captcha_provider_config["compass"]["client_keys"]["electron_key"] ?? "";
				} else {
					return $captcha_provider_config["comteam"]["client_keys"]["electron_key"] ?? "";
				}

			default:
				return $captcha_provider_config["compass"]["client_keys"]["default"] ?? "";
		}
	}
}