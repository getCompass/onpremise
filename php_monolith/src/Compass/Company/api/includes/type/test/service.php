<?php

namespace Compass\Company;

/**
 * Класс для работы с go_test
 */
class Type_Test_Service {

	public const WS_CONNECTION_STATUS_OPENED = 1; // статус открытого соединения
	public const WS_CONNECTION_STATUS_CLOSED = 2; // статус закрытого соединения

	// устанавливаем ws коннект
	public static function openConnect(int $user_id, string $token, string $ws_url, string $app_version = "0.0.1", string $method_config_hash = ""):void {

		$method = "test.openConnect";
		$ws_key = (string) $user_id . "_company_" . COMPANY_ID;
		$data   = [
			"token"              => $token,
			"ws_url"             => $ws_url,
			"user_id"            => $user_id,
			"ws_key"             => $ws_key,
			"app_version"        => $app_version,
			"method_config_hash" => $method_config_hash,
		];

		self::_doRequestToService($method, $data);
	}

	// закрываем ws коннект
	public static function closeConnect(int $user_id):void {

		$method = "test.closeConnect";
		$ws_key = (string) $user_id . "_company_" . COMPANY_ID;
		$data   = [
			"ws_key" => $ws_key,
		];

		self::_doRequestToService($method, $data);
	}

	// получаем статус ws коннекта
	public static function getStatus(int $user_id):int {

		$method = "test.getStatus";
		$ws_key = (string) $user_id . "_company_" . COMPANY_ID;
		$data   = [
			"ws_key" => $ws_key,
		];

		$response = self::_doRequestToService($method, $data);
		$response = fromJson($response);

		return $response["response"]["ws_status"];
	}

	// получаем список ивентов, которые были получены за время ws соединения
	public static function getEventList(int $user_id):array {

		$method = "test.getEventList";
		$ws_key = (string) $user_id . "_company_" . COMPANY_ID;
		$data   = [
			"ws_key" => $ws_key,
		];

		$response = self::_doRequestToService($method, $data);
		$response = fromJson($response);

		$ws_event_list = [];
		foreach ($response["response"]["ws_event_list"] as $v) {
			$ws_event_list[] = json_decode($v, false, JSON_FORCE_OBJECT);
		}

		return $ws_event_list;
	}

	// очищаем список ивентов, которые уже больше нам не нужны
	public static function doClearEventList(int $user_id):void {

		$method = "test.clearEventList";
		$ws_key = (string) $user_id . "_company_" . COMPANY_ID;
		$data   = [
			"ws_key" => $ws_key,
		];

		self::_doRequestToService($method, $data);
	}

	// отправляем ws ивент в соединение
	public static function sendWsEvent(int $user_id, array $ws_event):void {

		$method = "test.sendWsEvent";
		$ws_key = (string) $user_id . "_company_" . COMPANY_ID;
		$data   = [
			"ws_key"   => $ws_key,
			"ws_event" => $ws_event,
		];

		self::_doRequestToService($method, $data);
	}

	// очищаем список ивентов, которые уже больше нам не нужны
	public static function doCloseWS(int $user_id):void {

		$method = "test.closeConnect";
		$ws_key = (string) $user_id . "_company_" . COMPANY_ID;
		$data   = [
			"ws_key" => $ws_key,
		];

		self::_doRequestToService($method, $data);
	}

	// создаем publisher-соединение
	public static function createPublisher(int $user_id, string $connection_uuid):array {

		$method = "calls.createPublisher";
		$data   = [
			"user_id"         => $user_id,
			"connection_uuid" => $connection_uuid,
		];

		$response = self::_doRequestToService($method, $data);
		$response = fromJson($response);

		return [
			"offer" => [
				"sdp"  => $response,
				"type" => $response,
			],
		];
	}

	// создаем subscriber-соединение
	public static function createSubscriber(int $user_id, string $connection_uuid, array $offer):array {

		$method = "calls.createSubscriber";
		$data   = [
			"user_id"         => $user_id,
			"connection_uuid" => $connection_uuid,
			"offer"           => $offer,
		];

		$response = self::_doRequestToService($method, $data);
		$response = fromJson($response);

		return [
			"answer" => [
				"sdp"  => $response,
				"type" => $response,
			],
		];
	}

	// устанавливаем SDP Answer для publisher соединения
	public static function setAnswer(int $user_id, string $connection_uuid, array $answer):void {

		$method = "calls.setAnswer";
		$data   = [
			"user_id"         => $user_id,
			"connection_uuid" => $connection_uuid,
			"answer"          => (object) $answer,
		];

		self::_doRequestToService($method, $data);
	}

	// закрываем соединение
	public static function doHangUp(string $connection_uuid):void {

		$method = "calls.hangUp";
		$data   = [
			"connection_uuid" => $connection_uuid,
		];

		self::_doRequestToService($method, $data);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

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
		$curl   = new \Curl();
		return $curl->post(PUBLIC_ENTRYPOINT_GO_TEST, $params);
	}
}
