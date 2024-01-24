<?php

namespace Compass\Speaker;

/**
 * класс для работы с go_test
 */
class Gateway_Bus_Test {

	public const WS_CONNECTION_STATUS_OPENED = 1; // статус открытого соединения
	public const WS_CONNECTION_STATUS_CLOSED = 2; // статус закрытого соединения

	protected const _GRPC_CONNECTION_TIMEOUT = 1 * 1000000; // равно 1 секунда (1000ms) — gRPC требует значение в микросекундах

	// устанавливаем ws коннект
	public static function openConnect(int $user_id, string $token, string $ws_url):void {

		$request = new \TestGrpc\TestOpenConnectRequestStruct([
			"token"   => $token,
			"ws_url"  => $ws_url,
			"user_id" => $user_id,
		]);

		/** @var \TestGrpc\TestOpenConnectResponseStruct $response */
		$response = self::_doCallGrpc("TestOpenConnect", $request);
		$status   = $response[1];
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	// закрываем ws коннект
	public static function closeConnect(int $user_id):void {

		$request = new \TestGrpc\TestCloseConnectRequestStruct([
			"user_id" => $user_id,
		]);

		/** @var \TestGrpc\TestCloseConnectResponseStruct $response */
		$response = self::_doCallGrpc("TestCloseConnect", $request);
		$status   = $response[1];
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	// получаем статус ws коннекта
	public static function getStatus(int $user_id):int {

		$request = new \TestGrpc\TestGetStatusRequestStruct([
			"user_id" => $user_id,
		]);

		/** @var \TestGrpc\TestGetStatusResponseStruct $response */
		[$response, $status] = self::_doCallGrpc("TestGetStatus", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		return $response->getWsStatus();
	}

	// получаем список ивентов, которые были получены за время ws соединения
	public static function getEventList(int $user_id):array {

		$request = new \TestGrpc\TestGetEventListRequestStruct([
			"user_id" => $user_id,
		]);

		/** @var \TestGrpc\TestGetEventListResponseStruct $response */
		[$response, $status] = self::_doCallGrpc("TestGetEventList", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		// пробегаемся по результатам
		$output = [];
		foreach ($response->getWsEventList() as $v) {
			$output[] = json_decode($v, false, JSON_FORCE_OBJECT);
		}

		return $output;
	}

	// очищаем список ивентов, которые уже больше нам не нужны
	public static function doClearEventList(int $user_id):void {

		$request = new \TestGrpc\TestClearEventListRequestStruct([
			"user_id" => $user_id,
		]);

		/** @var \TestGrpc\TestClearEventListResponseStruct $response */
		$response = self::_doCallGrpc("TestClearEventList", $request);
		$status   = $response[1];
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	// отправляем ws ивент в соединение
	public static function sendWsEvent(int $user_id, array $ws_event):void {

		$request = new \TestGrpc\TestSendWsEventRequestStruct([
			"user_id"  => $user_id,
			"ws_event" => $ws_event,
		]);

		/** @var \TestGrpc\TestSendWsEventResponseStruct $response */
		$response = self::_doCallGrpc("TestSendWsEvent", $request);
		$status   = $response[1];
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	// создаем publisher-соединение
	public static function createPublisher(int $user_id, string $connection_uuid):array {

		$request = new \TestGrpc\CallsCreatePublisherRequestStruct([
			"user_id"         => $user_id,
			"connection_uuid" => $connection_uuid,
		]);

		/** @var \TestGrpc\CallsCreatePublisherResponseStruct $response */
		[$response, $status] = self::_doCallGrpc("CallsCreatePublisher", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		return [
			"offer" => [
				"sdp"            => $response->getOffer()->getSdp(),
				"type"           => $response->getOffer()->getType(),
			],
		];
	}

	// создаем subscriber-соединение
	public static function createSubscriber(int $user_id, string $connection_uuid, array $offer):array {

		$request = new \TestGrpc\CallsCreateSubscriberRequestStruct([
			"user_id"         => $user_id,
			"connection_uuid" => $connection_uuid,
			"offer"           => new \TestGrpc\offerStruct($offer),
		]);

		/** @var \TestGrpc\CallsCreateSubscriberResponseStruct $response */
		[$response, $status] = self::_doCallGrpc("CallsCreateSubscriber", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		return [
			"answer" => [
				"sdp"            => $response->getAnswer()->getSdp(),
				"type"           => $response->getAnswer()->getType(),
			],
		];
	}

	// устанавливаем SDP Answer для publisher соединения
	public static function setAnswer(int $user_id, string $connection_uuid, array $answer):void {

		$request = new \TestGrpc\CallsSetAnswerRequestStruct([
			"user_id"         => $user_id,
			"connection_uuid" => $connection_uuid,
			"answer"          => new \TestGrpc\answerStruct($answer),
		]);

		/** @var \TestGrpc\CallsSetAnswerResponseStruct $response */
		$response = self::_doCallGrpc("CallsSetAnswer", $request);
		$status   = $response[1];
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	// получаем SDP Offer существующего publisher соединения
	public static function getOffer(int $user_id, string $connection_uuid) {

		// формируем запрос
		$request = new \TestGrpc\CallsGetOfferRequestStruct([
			"user_id"         => $user_id,
			"connection_uuid" => $connection_uuid,
		]);

		/** @var \TestGrpc\CallsGetOfferResponseStruct $response */
		[$response, $status] = self::_doCallGrpc("CallsGetOffer", $request);
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}

		return [
			"offer" => [
				"sdp"            => $response->getOffer()->getSdp(),
				"type"           => $response->getOffer()->getType(),
			],
		];
	}

	// закрываем соединение
	public static function doHangUp(string $connection_uuid):void {

		$request = new \TestGrpc\CallsHangUpRequestStruct([
			"connection_uuid" => $connection_uuid,
		]);

		/** @var \TestGrpc\CallsHangUpResponseStruct $response */
		$response = self::_doCallGrpc("CallsHangUp", $request);
		$status   = $response[1];
		if ($status->code !== \Grpc\STATUS_OK) {
			throw new \busException("undefined error_code in " . __CLASS__ . " code " . $status->code);
		}
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// делаем grpc запрос к указанному методу с переданными данными
	protected static function _doCallGrpc(string $method_name, \Google\Protobuf\Internal\Message $request):array {

		$connection = ShardingGateway::rpc("test", \TestGrpc\testClient::class);

		return $connection->callGrpc($method_name, $request);
	}
}
