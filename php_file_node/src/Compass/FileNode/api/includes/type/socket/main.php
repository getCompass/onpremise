<?php

namespace Compass\FileNode;

/**
 * задача класса общаться между проектами
 * универсальная функция - общение между серверами
 */
class Type_Socket_Main {

	// !!! НЕ МЕНЯТЬ !!!
	// таймаут всех socket соединений между модулями - 10 секунд
	protected const _CURL_TIMEOUT = 10;

	// обратиться к методу
	// user_id - текущий пользователь Compass, по чьей просьбе делается этот запрос
	public static function doCall(string $url, string $method, string $json_params, string $signature, int $user_id = 0, int $company_id = 0):array {

		// формируем сообщение
		$ar_post = [
			"method"        => $method,
			"user_id"       => $user_id,
			"sender_module" => CURRENT_MODULE,
			"json_params"   => $json_params,
			"signature"     => $signature,
		];

		if ($company_id > 0) {
			$ar_post["company_id"] = $company_id;
		}

		// инициализируем curl
		$curl = self::_getCurl();

		// делаем запрос
		try {
			$html = $curl->post($url, $ar_post);
		} catch (\cs_CurlError $e) {

			self::_onSocketRequestFailed($curl->getResponseCode(), $url, $method, $e->getMessage());
		}
		$response = fromJson($html);

		// если не вернулся status
		if (!isset($response["status"])) {
			self::_onSocketRequestFailed($curl->getResponseCode(), $url, $method, $html);
		}

		// если не вернулся status
		if ($response["status"] === "error" && isset($response["http_code"])) {
			self::_onSocketRequestFailed($curl->getResponseCode(), $url, $method, $html);
		}

		return [$response["status"], $response["response"]];
	}

	// инициализируем cURL
	protected static function _getCurl():\Curl {

		$curl = new \Curl();
		$curl->setTimeout(self::_CURL_TIMEOUT);
		$curl->needVerify();

		// необходимо для комфортного дебага + удобный функционал на любой окружении манипулировать таймаутами
		// локально устанавливается в docker/docker-compose.yml::app.environment
		// на остальных окруженях через export переменной окружения
		if (isset($_ENV["PHP_FPM_TIMEOUT"])) {
			$curl->setTimeout((int) $_ENV["PHP_FPM_TIMEOUT"]);
		}

		return $curl;
	}

	// записываем лог и выбрасываем ошибку
	protected static function _onSocketRequestFailed(int $http_code, string $url, string $method, string $message):void {

		Type_System_Admin::log("socket_request_error", [
			"url"       => $url,
			"http_code" => $http_code,
			"method"    => $method,
			"message"   => $message,
		]);

		throw new \cs_SocketRequestIsFailed(
			$http_code, $url, [], "Socket request '{$method}' failed; HTTP CODE: {$http_code}; Message: '{$message}'"
		);
	}
}
