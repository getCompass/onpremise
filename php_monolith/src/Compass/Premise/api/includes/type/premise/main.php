<?php

namespace Compass\Premise;

/**
 * задача класса общаться между проектами
 * универсальная функция - общение между серверами
 */
class Type_Premise_Main {

	protected const _CURL_TIMEOUT = 10;

	// обратиться к методу
	public static function doCall(string $url, string $method, string $json_params, string $signature, string $domain_hash, string $server_uid):array {

		// формируем сообщение
		$ar_post = [
			"method"      => $method,
			"domain_hash" => $domain_hash,
			"server_uid"  => $server_uid,
			"json_params" => $json_params,
			"signature"   => $signature,
		];

		// инициализируем curl
		$curl = self::_getCurl();

		// делаем запрос
		try {
			$html = $curl->post($url, $ar_post);
		} catch (\cs_CurlError $e) {
			self::_onPremiseRequestFailed($curl->getResponseCode(), $url, [], $method, $e->getMessage());
		}
		$response = fromJson($html);

		// если не вернулся status
		if (!isset($response["status"])) {
			self::_onPremiseRequestFailed($curl->getResponseCode(), $url, $response, $method, $html);
		}

		// если не вернулся status
		if ($response["status"] === "error" && isset($response["http_code"])) {
			self::_onPremiseRequestFailed($curl->getResponseCode(), $url, $response, $method, $html);
		}

		return [$response["status"], $response["response"]];
	}

	// инициализируем cURL
	protected static function _getCurl():\Curl {

		$curl = new \Curl();
		$curl->setTimeout(self::_CURL_TIMEOUT);

		// включаем верификацию хоста
		//$curl->needVerify();

		// необходимо для комфортного дебага + удобный функционал на любой окружении манипулировать таймаутами
		// локально устанавливается в compass_docker/docker-compose.yml::app.environment
		// на остальных окруженях через export переменной окружения
		if (isset($_ENV["PHP_FPM_TIMEOUT"])) {
			$curl->setTimeout((int) $_ENV["PHP_FPM_TIMEOUT"]);
		}

		return $curl;
	}

	// записываем лог и выбрасываем ошибку
	protected static function _onPremiseRequestFailed(int $http_status_code, string $url, array $response, string $method, string $message):void {

		Type_System_Admin::log("socket_request_error", [
			"url"       => $url,
			"http_code" => $http_status_code,
			"method"    => $method,
			"message"   => $message,
		]);

		throw new \cs_SocketRequestIsFailed(
			$http_status_code, $url, $response, "Socket request '{$method}' failed; HTTP CODE: {$http_status_code}; Message: '{$message}'"
		);
	}
}
