<?php

namespace BaseFrame\Socket;

/**
 * задача класса общаться между проектами
 * универсальная функция - общение между серверами
 */
class Main {

	// таймаут всех socket соединений между модулями - 10 секунд
	protected const _CURL_TIMEOUT = 10;

	// обратиться к методу
	// user_id - текущий пользователь Compass, по чьей просьбе делается этот запрос
	public static function doCall(string $url, string $method, string $json_params, string $signature, string $current_module, int $company_id = 0, int $user_id = 0):array {

		// формируем сообщение
		$ar_post = [
			"method"        => $method,
			"company_id"    => $company_id,
			"user_id"       => $user_id,
			"sender_module" => $current_module,
			"json_params"   => $json_params,
			"signature"     => $signature,
		];

		// добавим передачу по сокету компании - чтобы общаться не по домену
		$headers = ["X-COMPANY-ID" => $company_id];

		// инициализируем curl
		$curl = self::_getCurl();

		// делаем запрос
		try {
			$html = $curl->post($url, $ar_post, $headers);
		} catch (\cs_CurlError $e) {
			self::_onSocketRequestFailed($curl->getResponseCode(), $url, [], $method, $e->getMessage());
		}
		$response = fromJson($html);

		// если не вернулся status
		if (!isset($response["status"]) || ($response["status"] === "error" && isset($response["http_code"]))) {
			self::_onSocketRequestFailed($curl->getResponseCode(), $url, $response, $method, $html);
		}

		return [$response["status"], $response["response"]];
	}

	// инициализируем cURL
	protected static function _getCurl():\Curl {

		$curl = new \Curl();
		$curl->setTimeout(self::_CURL_TIMEOUT);
		$curl->needVerify();
		$curl->setCaCertificate(SocketProvider::caCertificate());

		// необходимо для комфортного дебага + удобный функционал на любой окружении манипулировать таймаутами
		// локально устанавливается в compose.yml::app.environment
		// на остальных окруженях через export переменной окружения
		if (isset($_ENV["PHP_FPM_TIMEOUT"])) {
			$curl->setTimeout((int) $_ENV["PHP_FPM_TIMEOUT"]);
		}

		return $curl;
	}

	// записываем лог и выбрасываем ошибку
	protected static function _onSocketRequestFailed(int $http_status_code, string $url, array $response, string $method, string $message):void {

		throw new \cs_SocketRequestIsFailed(
			$http_status_code, $url, $response, "Socket request '{$method}' failed; HTTP CODE: {$http_status_code}; Message: '{$message}'"
		);
	}
}
