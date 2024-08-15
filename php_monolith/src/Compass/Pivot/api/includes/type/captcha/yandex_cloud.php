<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Проверка yandex-капчи
 *
 * Class Type_Captcha_YandexCloud
 */
class Type_Captcha_YandexCloud extends Type_Captcha_Main {

	public const NAME = "yandex_cloud";

	/** @var string URL API yandex-cloud-captcha */
	protected const _URL = "https://smartcaptcha.yandexcloud.net/validate";

	protected const _REQUEST_TIMEOUT = 20;

	/**
	 * проверить, что пользователь прошел проверку
	 *
	 */
	public function check(string $captcha, string $platform, string $user_action = "check_captcha"):bool {

		// если конфига нет - завершаем выполнение
		if ($this->_getConfig() === []) {
			return true;
		}

		// если НЕ продакшн и ответ капчи из списка замоканных токенов
		if (false === ServerProvider::isProduction() && in_array($captcha, Type_Captcha_YandexCloudMock::MOCK_CAPTCHA_TOKEN_LIST)) {
			return $captcha == Type_Captcha_YandexCloudMock::MOCK_SUCCESS_CAPTCHA_TOKEN;
		}

		try {
			$response = $this->_makeRequest(self::_URL, $this->_getRequestData($captcha));
		} catch (\cs_CurlError) {
			return false;
		}

		return $response["status"] == "ok";
	}

	/**
	 * Сфоримровать массив для запроса
	 */
	#[ArrayShape(["secret" => "string", "token" => "string", "ip" => "string"])]
	protected function _getRequestData(string $captcha):array {

		return [
			"secret" => $this->_getServerCaptchaKey(),
			"token"  => $captcha,
			"ip"     => getIp(),
		];
	}

	/**
	 * Получить приватный ключ для отправки запроса
	 */
	protected function _getServerCaptchaKey():string {

		$captcha_provider_config = $this->_getConfig();
		$app_field_name          = $this->_getAppName();

		return $captcha_provider_config[$app_field_name]["server_key"] ?? "";
	}

	/**
	 * сделать запрос
	 *
	 * @throws \cs_CurlError
	 */
	protected function _makeRequest(string $url, array $ar_post):array {

		$args = http_build_query($ar_post);
		$url  = "{$url}?{$args}";

		// инстанс курла
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, self::_REQUEST_TIMEOUT);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
			"Content-Type: application/json; charset=utf-8",
		], []));
		$response = curl_exec($ch);

		return fromJson($response);
	}

	/**
	 * Получить публчиный ключ для капчи.
	 */
	public function getPublicCaptchaKey(string $platform = ""):string {

		$captcha_provider_config = $this->_getConfig();

		$client_key = match ($platform) {
			default => $captcha_provider_config["compass"]["client_keys"]["default"] ?? "",
		};

		// если сервер онпрема и полученный ключ пустой, то пробуем достать дефолт клиентский ключ
		// (кейс, когда пользователь на своём onpremise-сервере заполнил в конфиге только дефолт клиентский ключ)
		if (ServerProvider::isOnPremise() && mb_strlen($client_key) < 1) {
			return $captcha_provider_config["compass"]["client_keys"]["default"] ?? "";
		}

		return $client_key;
	}
}