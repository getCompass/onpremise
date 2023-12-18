<?php

namespace Compass\Speaker;

/**
 * Класс для работы с go-mock-service
 */
class Type_Mock_Service {

	/** @var string адрес мок сервиса */
	protected const PATH = "http://go-mock-service-test/";

	/**
	 * Сделать запрос в мок сервис
	 *
	 * @throws \cs_CurlError
	 */
	public static function makeRequest(string $key, array $data):string {

		return self::_doRequestToService("mock.setMockResponse", [
			"key"      => $key,
			"response" => toJson($data),
		]);
	}

	/**
	 * Получить сделанный запрос в мок сервис по ключу
	 *
	 * @throws \cs_CurlError
	 */
	public static function getRequestByKey(string $key):string {

		return self::_doRequestToService("mock.getMockResponse", [
			"key" => $key,
		]);
	}

	/**
	 * Получить сделанный запрос в мок сервис по ключу
	 *
	 * @throws \cs_CurlError
	 */
	public static function getAllRequest():array {

		$response = self::_doRequestToService("mock.getAllMockResponse", []);
		$temp     = fromJson($response);

		return fromJson($temp["response"]["response"]);
	}

	/**
	 * устанавливаем ожидаемый респонс с мока
	 *
	 * @throws \cs_CurlError
	 */
	public static function setExpectResponseFromMock(string $key, array $response):void {

		self::_doRequestToService("mock.setExpectResponseFromMock", [
			"key"      => $key,
			"response" => toJson($response),
		]);
	}

	/**
	 * сбрасываем ранее замоканные по ключу данные
	 *
	 * @throws \cs_CurlError
	 */
	public static function resetRequest(string $key):void {

		self::_doRequestToService("mock.resetMockResponse", [
			"key" => $key,
		]);
	}

	/**
	 * совершаем запрос
	 *
	 * @throws \cs_CurlError
	 */
	protected static function _doRequestToService(string $method, array $ar_post):string {

		$params = [
			"method"  => "$method",
			"request" => toJson($ar_post),
		];

		$curl = new \Curl();
		return $curl->post(self::PATH, $params);
	}

	/**
	 * Очистить мок
	 *
	 * @throws \cs_CurlError
	 */
	public static function clear():void {

		self::_doRequestToService("mock.clearMock", []);
	}
}