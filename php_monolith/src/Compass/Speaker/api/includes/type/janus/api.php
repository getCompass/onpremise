<?php

namespace Compass\Speaker;

use JetBrains\PhpStorm\ArrayShape;

/**
 * класс-интерфейс для работы с нодой janus-gateway
 */
class Type_Janus_Api extends Type_Janus_Default {

	// переменная для синглтон объекта curl
	protected $_curl = null;

	// максимальный битрейт для разговорной комнаты
	public const MAX_ROOM_BITRATE = 512 * 1000;

	// таймаут для установления соединения и работы с janus-gateway
	protected const _REQUEST_TIMEOUT    = 2;
	protected const _CONNECTION_TIMEOUT = 3;

	// плагин, через который осуществляется работа с разговорными комнатами
	protected const _VIDEO_ROOM_PLUGIN_NAME = "janus.plugin.videoroom";

	// функция инициализирует сессию для работы с janus-gateway
	public function initSession():int {

		// формируем параметры запроса
		$ar_post = [
			"janus"       => "create",
			"transaction" => generateUUID(),
		];

		// совершаем запрос <node_host>/janus
		$response = $this->_doJanusPost($this->node->janus_path, $ar_post);

		// проверяем, что janus-gateway вернул корректный response c сессией
		if ($response["janus"] != "success" || !isset($response["data"])) {
			throw new cs_FailedJanusGatewayAPIRequest($this->node->node_id, $response);
		}

		return $response["data"]["id"];
	}

	// функция закрепляет плагин за сессией и возвращает идентификатор обработчика плагина
	// для дальнейшего взаимодействия с ним
	public function initPluginHandle(int $session_id):int {

		// формируем параметры запроса
		$ar_post = [
			"janus"       => "attach",
			"plugin"      => self::_VIDEO_ROOM_PLUGIN_NAME,
			"transaction" => generateUUID(),
			"opaque_id"   => \CompassApp\System\Company::getCompanyDomain(),
		];

		// совершаем запрос <node_host>/janus/<session_id>
		$response = $this->_doJanusPost("{$this->node->janus_path}/{$session_id}", $ar_post);

		// проверяем, что janus-gateway вернул корректный response c сессией
		if ($response["janus"] != "success" || !isset($response["data"])) {
			throw new cs_FailedJanusGatewayAPIRequest($this->node->node_id, $response);
		}

		return $response["data"]["id"];
	}

	// функция уничтожает обработчик плагина закрепленный за сессией
	public function detachPluginHandle(int $session_id, int $handle_id):void {

		// формируем параметры запроса
		$ar_post = [
			"janus"       => "detach",
			"transaction" => generateUUID(),
		];

		// совершаем запрос <node_host>/janus/<session_id>/<plugin_handle_id>
		$response = $this->_doJanusPost("{$this->node->janus_path}/{$session_id}/{$handle_id}", $ar_post);

		// проверяем, что janus-gateway вернул корректный response c сессией
		if ($response["janus"] != "success") {
			throw new cs_FailedJanusGatewayAPIRequest($this->node->node_id, $response);
		}
	}

	// уничтожить сессию
	public function destroySession(int $session_id):void {

		// формируем параметры запроса
		$ar_post = [
			"janus"       => "destroy",
			"transaction" => generateUUID(),
		];

		// совершаем запрос <node_host>/janus/<session_id>
		try {

			$response = $this->_doJanusPost("{$this->node->janus_path}/{$session_id}", $ar_post);
		} catch (\returnException) {
			throw new cs_FailedJanusGatewayAPIRequest($this->node->node_id, []);
		}

		// проверяем, что janus-gateway вернул корректный response c сессией
		if ($response["janus"] != "success") {
			throw new cs_FailedJanusGatewayAPIRequest($this->node->node_id, $response);
		}
	}

	// отправить trickle (ice-candidate)
	public function sendTrickle(int $session_id, int $handle_id, array $candidate, bool $is_many):void {

		// формируем параметры запроса
		$ar_post = [
			"janus"       => "trickle",
			"transaction" => generateUUID(),
			"token"       => Type_Janus_Node::getUserToken($session_id),
		];

		if ($is_many) {
			$ar_post["candidates"] = $candidate;
		} else {
			$ar_post["candidate"] = $candidate;
		}

		// совершаем запрос <node_host>/janus/<session_id>/<plugin_handle_id>
		$this->_doJanusPost("{$this->node->janus_path}/{$session_id}/{$handle_id}", $ar_post);
	}

	// отправить completed trickle (ice-candidate)
	public function sendCompletedTrickle(int $session_id, int $handle_id):void {

		// формируем параметры запроса
		$ar_post = [
			"janus"       => "trickle",
			"transaction" => generateUUID(),
			"candidate"   => [
				"completed" => true,
			],
			"token"       => Type_Janus_Node::getUserToken($session_id),
		];

		// совершаем запрос <node_host>/janus/<session_id>/<plugin_handle_id>
		$this->_doJanusPost("{$this->node->janus_path}/{$session_id}/{$handle_id}", $ar_post);
	}

	// отправить сообщение в обработчик плагина
	public function sendMessage(int $session_id, int $handle_id, array $body):array {

		// формируем параметры запроса
		$ar_post = $this->_prepareMessageRequestParams($body);

		// совершаем запрос <node_host>/janus/<session_id>/<plugin_handle_id>
		return $this->_doJanusPost("{$this->node->janus_path}/{$session_id}/{$handle_id}", $ar_post);
	}

	// создать комнату
	public function createRoom(int $session_id, int $handle_id):int {

		$codec_list = self::_getJanusCodecList();

		// создаем комнату
		$response = $this->sendMessage($session_id, $handle_id, [
			"request"               => "create",
			"publishers"            => 16,
			"fir_freq"              => 10,
			"bitrate"               => self::MAX_ROOM_BITRATE,
			"audiocodec"            => $codec_list["audio_codec_list"],
			"videocodec"            => $codec_list["video_codec_list"],
			"transport_wide_cc_ext" => true,
		]);

		if ($response["janus"] != "success") {

			if (isset($response["error"])) {
				$this->_switchJanusErrorCode(__METHOD__, $session_id, $handle_id, $response["error"]["code"], $response["error"]["reason"]);
			}

			throw new \returnException(__METHOD__ . ": room not created");
		}

		return $response["plugindata"]["data"]["room"];
	}

	// получаем поддерживаемые janus кодеки
	protected static function _getJanusCodecList():array {

		$codec_config_list = getConfig("JANUS_CODEC_LIST");

		// получаем из массива строку
		$codec_list["audio_codec_list"] = implode(",", $codec_config_list["audio_codec_list"]);
		$codec_list["video_codec_list"] = implode(",", $codec_config_list["video_codec_list"]);

		return $codec_list;
	}

	// уничтожить комнату
	public function destroyRoom(int $session_id, int $handle_id, int $room_id):void {

		// уничтожаем комнату
		$response = $this->sendMessage($session_id, $handle_id, [
			"request" => "destroy",
			"room"    => $room_id,
		]);

		// если янус не вернул успешный ответ
		if ($response["janus"] != "success") {
			throw new cs_FailedJanusGatewayAPIRequest($this->node->node_id, $response);
		}
	}

	// вступить в комнату как publisher
	public function joinRoomAsPublisher(int $session_id, int $handle_id, int $room_id, int $bitrate, int $participant_id):void {

		// вступаем в комнату
		$response = $this->sendMessage($session_id, $handle_id, [
			"request" => "join",
			"ptype"   => "publisher",
			"bitrate" => $bitrate,
			"room"    => $room_id,
			"id"      => $participant_id,
		]);

		// проверяем ответ
		if ($response["janus"] != "ack") {

			if (isset($response["error"])) {
				$this->_switchJanusErrorCode(__METHOD__, $session_id, $handle_id, $response["error"]["code"], $response["error"]["reason"]);
			}

			throw new \returnException(__METHOD__ . ": janus not joined user as publisher");
		}
	}

	// вступить в комнату как subscriber
	public function joinRoomAsSubscriber(int $session_id, int $handle_id, int $room_id, int $publisher_user_id, int $participant_id):void {

		$ar_post               = $this->_prepareMessageRequestParams([
			"request" => "join",
			"ptype"   => "subscriber",
			"room"    => $room_id,
			"id"      => $participant_id,
			"feed"    => $publisher_user_id,
		]);
		$ar_post["token"]      = Type_Janus_Node::getUserToken($session_id);
		$ar_post["room_token"] = Type_Janus_Node::getRoomToken($room_id, $participant_id, $publisher_user_id);

		// совершаем запрос <node_host>/janus/<session_id>/<plugin_handle_id>
		$response = $this->_doJanusPost("{$this->node->janus_path}/{$session_id}/{$handle_id}", $ar_post);

		// проверяет ответ
		if ($response["janus"] != "ack") {

			if (isset($response["error"])) {
				$this->_switchJanusErrorCode(__METHOD__, $session_id, $handle_id, $response["error"]["code"], $response["error"]["reason"]);
			}

			throw new \returnException(__METHOD__ . ": janus not joined user as subscriber");
		}
	}

	// начать слушать publisher
	public function startListenPublisher(int $session_id, int $handle_id, array $answer):void {

		$ar_post          = $this->_prepareMessageRequestParams([
			"request" => "start",
		]);
		$ar_post["jsep"]  = $answer;
		$ar_post["token"] = Type_Janus_Node::getUserToken($session_id);

		// совершаем запрос <node_host>/janus/<session_id>/<plugin_handle_id>
		$response = $this->_doJanusPost("{$this->node->janus_path}/{$session_id}/{$handle_id}", $ar_post);

		// проверяет ответ
		if ($response["janus"] != "ack") {

			if (isset($response["error"])) {
				$this->_switchJanusErrorCode(__METHOD__, $session_id, $handle_id, $response["error"]["code"], $response["error"]["reason"]);
			}

			throw new \returnException(__METHOD__ . ": janus not start listen to publisher");
		}
	}

	// начать передачу медиаданных
	public function startPublish(int $session_id, int $handle_id, array $offer, string $audio_codec, string $video_codec, bool $audio = false, bool $video = false):void {

		$ar_post          = $this->_prepareMessageRequestParams([
			"request"    => "publish",
			"audio"      => $audio,
			"video"      => $video,
			"audiocodec" => $audio_codec,
			"videocodec" => $video_codec,
		]);
		$ar_post["jsep"]  = $offer;
		$ar_post["token"] = Type_Janus_Node::getUserToken($session_id);

		// совершаем запрос <node_host>/janus/<session_id>/<plugin_handle_id>
		$response = $this->_doJanusPost("{$this->node->janus_path}/{$session_id}/{$handle_id}", $ar_post);

		// проверяем ответ
		if ($response["janus"] != "ack") {

			if (isset($response["error"])) {
				$this->_switchJanusErrorCode(__METHOD__, $session_id, $handle_id, $response["error"]["code"], $response["error"]["reason"]);
			}

			throw new \returnException(__METHOD__ . ": janus not start publish");
		}
	}

	/**
	 * переконфигурировать соединение
	 * например, когда publisher участнику необходимо изменить SDP пакет или провести реконнект
	 *
	 * @throws cs_FailedJanusGatewayAPIRequest
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function makeConfigure(int $session_id, int $handle_id, bool $audio, bool $video, array $offer):void {

		$ar_post          = $this->_prepareMessageRequestParams([
			"request" => "configure",
			"audio"   => $audio,
			"video"   => $video,
		]);
		$ar_post["jsep"]  = $offer;
		$ar_post["token"] = Type_Janus_Node::getUserToken($session_id);

		// совершаем запрос <node_host>/janus/<session_id>/<plugin_handle_id>
		$response = $this->_doPost("{$this->node->janus_path}/{$session_id}/{$handle_id}", $ar_post);
		if (!isset($response["janus"])) {

			$response = toJson($response);
			throw new \returnException(__METHOD__ . ": unexpected response:\r\n$response");
		}

		// проверяем ответ
		if ($response["janus"] != "ack") {

			if (isset($response["error"])) {
				$this->_switchJanusErrorCode(__METHOD__, $session_id, $handle_id, $response["error"]["code"], $response["error"]["reason"]);
			}

			throw new \returnException(__METHOD__ . ": janus not accept configure");
		}
	}

	// конфигур соединения
	public function configure(int $session_id, int $handle_id, array $params):void {

		// запрос в janus по-умолчанию
		$params["request"] = "configure";
		$ar_post           = $this->_prepareMessageRequestParams($params);
		$ar_post["token"]  = Type_Janus_Node::getUserToken($session_id);

		// совершаем запрос <node_host>/janus/<session_id>/<plugin_handle_id>
		$response = $this->_doJanusPost("{$this->node->janus_path}/{$session_id}/{$handle_id}", $ar_post);

		// проверяем ответ
		if ($response["janus"] != "ack") {

			if (isset($response["error"])) {
				$this->_switchJanusErrorCode(__METHOD__, $session_id, $handle_id, $response["error"]["code"], $response["error"]["reason"]);
			}

			throw new \returnException(__METHOD__ . ": janus not accept configure");
		}
	}

	// редактируем комнату
	public function editRoom(int $session_id, int $handle_id, array $params = []):void {

		// запрос в janus по-умолчанию
		$params["request"] = "edit";

		// отправляем запрос
		$response = $this->sendMessage($session_id, $handle_id, $params);

		// проверяем ответ
		if ($response["janus"] != "success") {

			if (isset($response["error"])) {
				$this->_switchJanusErrorCode(__METHOD__, $session_id, $handle_id, $response["error"]["code"], $response["error"]["reason"]);
			}

			throw new \returnException(__METHOD__ . ": janus not edit room");
		}
	}

	/**
	 * разговорная комната существует?
	 *
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public function isRoomExists(int $session_id, int $handle_id, array $params = []):bool {

		// запрос в janus по-умолчанию
		$params["request"] = "exists";

		// отправляем запрос
		$response = $this->sendMessage($session_id, $handle_id, $params);

		// проверяем ответ
		if ($response["janus"] != "success") {

			if (isset($response["error"])) {
				$this->_switchJanusErrorCode(__METHOD__, $session_id, $handle_id, $response["error"]["code"], $response["error"]["reason"]);
			}

			throw new \returnException(__METHOD__ . ": janus not edit room");
		}

		return (bool) $response["plugindata"]["data"]["exists"] ?? 0;
	}

	// отправить сообщение в админ handler
	public function sendAdminRequest(string $janus_request, int $session_id = null, int $handle_id = null):array {

		// формируем параметры запроса
		$ar_post = [
			"janus"       => $janus_request,
			"transaction" => generateUUID(),
		];

		if (!is_null($session_id)) {
			$ar_post["session_id"] = $session_id;
		}
		if (!is_null($handle_id)) {
			$ar_post["handle_id"] = $handle_id;
		}

		// совершаем запрос <node_host>/admin/<session_id>/<plugin_handle_id>
		return $this->_doAdminPost($ar_post);
	}

	// функция осуществляет get запрос к ноде
	public function doGet(string $path, string $token):array {

		// получаем протокол для запроса; собираем url и тело запроса
		$protocol = Type_Janus_Node::getProtocol($this->node->is_ssl);
		$url      = "{$protocol}{$this->node->host}:{$this->node->port}/{$path}";

		// инициализируем curl
		$curl = $this->_getCurl(true);
		curl_setopt($curl, CURLOPT_URL, $url);

		curl_setopt($this->_curl, CURLOPT_HTTPHEADER, [
			"token: {$token}",
		]);

		// совершаем запрос
		$response = curl_exec($curl);
		$response = fromJson($response);

		// если ответ не равен 200, то значит ошибка
		$response_http_status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($response_http_status_code != 200 || (!isset($response["janus"]) && !is_array($response))) {

			throw new \returnException(__METHOD__ . ": failed janus-gateway request. HTTP_STATUS_CODE: {$response_http_status_code}\n" . curl_error($curl));
		}

		return $response;
	}

	// начинаем трейсить сеть до участника звонка
	public function doStartMtr(string $key, string $ip_address):void {

		//
		$ar_post = [
			"request"    => "do_start_mtr",
			"key"        => $key,
			"ip_address" => $ip_address,
		];

		// совершаем запрос <node_host>/janus
		$response = $this->_doPost($this->node->janus_path, $ar_post);

		// проверяем, что janus-gateway вернул корректный response
		if (!isset($response["status"]) || $response["status"] != "ok") {
			throw new \returnException("request was failed");
		}
	}

	// останавливаем трейсить сеть до участника звонка
	public function doStopMtr(string $key):void {

		//
		$ar_post = [
			"request" => "do_stop_mtr",
			"key"     => $key,
		];

		// совершаем запрос <node_host>/janus
		$response = $this->_doPost($this->node->janus_path, $ar_post);

		// проверяем, что janus-gateway вернул корректный response
		if (!isset($response["status"]) || $response["status"] != "ok") {
			throw new \returnException("request was failed");
		}
	}

	// получаем результаты трейса
	public function getResultMtr(string $key):array {

		//
		$ar_post = [
			"request" => "get_result_mtr",
			"key"     => $key,
		];

		// совершаем запрос <node_host>/janus
		$response = $this->_doPost($this->node->janus_path, $ar_post);

		// проверяем, что janus-gateway вернул корректный response
		if (!isset($response["status"]) || $response["status"] != "ok") {
			throw new \returnException("request was failed");
		}

		return $response;
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// подготовливаем параметры к запросу janus message
	#[ArrayShape(["janus" => "string", "transaction" => "string", "body" => "array"])]
	protected function _prepareMessageRequestParams(array $body):array {

		return [
			"janus"       => "message",
			"transaction" => generateUUID(),
			"body"        => $body,
		];
	}

	// функция осуществляет post запрос к ноде
	public function _doJanusPost(string $path, array $payload):array {

		try {
			$response = $this->_doPost($path, $payload);
		} catch (cs_FailedJanusGatewayAPIRequest $e) {
			throw new \returnException(__METHOD__ . ": failed janus-gateway request. HTTP_STATUS_CODE: " . $e->getCode());
		}
		if (!isset($response["janus"])) {

			$response = toJson($response);
			throw new \returnException(__METHOD__ . ": unexpected response:\r\n$response");
		}

		return $response;
	}

	/**
	 * функция собирает url и делает post запрос
	 *
	 * @throws cs_FailedJanusGatewayAPIRequest
	 */
	protected function _doPost(string $path, array $payload):array {

		if (Type_System_Testing::forceFailedJanusRequest()) {
			throw new cs_FailedJanusGatewayAPIRequest($this->node->node_id, [], "failed janus-gateway request");
		}

		$payload["apisecret"] = $this->node->api_secret;
		$ar_post              = toJson($payload);

		// получаем протокол для запроса; собираем url и тело запроса
		$protocol = Type_Janus_Node::getProtocol($this->node->is_ssl);
		$url      = "{$protocol}{$this->node->host}:{$this->node->port}/{$path}/";

		// инициализируем curl
		$curl = $this->_getCurl();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $ar_post);

		// совершаем запрос
		$response = curl_exec($curl);

		// если ответ не равен 200, то значит ошибка
		$response_http_status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($response_http_status_code != 200) {

			if ($response_http_status_code == 0) {
				Type_System_Admin::log("failed_janus_request", ["error" => curl_error($curl)]);
			}

			throw new cs_FailedJanusGatewayAPIRequest($this->node->node_id, fromJson($response), "failed janus-gateway request", $response_http_status_code);
		}

		return fromJson($response);
	}

	// функция осуществляет post запрос к admin модулю ноды
	protected function _doAdminPost(array $payload):array {

		$payload["admin_secret"] = $this->node->admin_secret;
		$ar_post                 = toJson($payload);

		// получаем протокол для запроса; собираем url и тело запроса
		$protocol = Type_Janus_Node::getProtocol($this->node->is_ssl);
		$url      = "{$protocol}{$this->node->host}:{$this->node->port}/{$this->node->janus_admin_path}/";

		// инициализируем curl
		$curl = $this->_getCurl();
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $ar_post);

		// совершаем запрос
		$response = curl_exec($curl);
		$response = fromJson($response);

		// если ответ не равен 200, то значит ошибка
		$response_http_status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($response_http_status_code != 200 || !isset($response["janus"])) {

			throw new \returnException(__METHOD__ . ": failed janus-gateway request. HTTP_STATUS_CODE: {$response_http_status_code}\n" . curl_error($curl));
		}

		return $response;
	}

	/**
	 * инициализируем cURL
	 *
	 */
	protected function _getCurl(bool $is_get_request = false):mixed {

		if (!is_null($this->_curl)) {

			$this->_setCurlRequest($is_get_request);
			return $this->_curl;
		}

		// инициируем curl
		$this->_curl = curl_init();

		// задаем заголовки
		curl_setopt($this->_curl, CURLOPT_HTTPHEADER, [
			"Content-type: application/json",
			"Expect:",
		]);

		// задаем опции
		curl_setopt($this->_curl, CURLOPT_RETURNTRANSFER, true);

		// устанавливаем опции post or get для curl
		$this->_setCurlRequest($is_get_request);

		return $this->_curl;
	}

	// устанавливаем опции для curl
	protected function _setCurlRequest(bool $is_get_request):void {

		if ($is_get_request) {

			curl_setopt($this->_curl, CURLOPT_CUSTOMREQUEST, "GET");
			return;
		}

		curl_setopt($this->_curl, CURLOPT_CONNECTTIMEOUT, self::_CONNECTION_TIMEOUT);
		curl_setopt($this->_curl, CURLOPT_TIMEOUT, self::_REQUEST_TIMEOUT);
		curl_setopt($this->_curl, CURLOPT_CUSTOMREQUEST, "POST");
	}

	// определяем код ошибки от janus
	public function _switchJanusErrorCode(string $action, int $session_id, int $handle_id, int $error_code, string $response = ""):void {

		$error_config = getConfig("JANUS_ERROR_LIST");

		// если отсутствует полученная ошибка
		if (!isset($error_config[$error_code])) {

			throw new \parseException("unknown error_code ({$error_code}). Janus response: {$response}");
		}

		Type_System_Admin::log("debug_janus_500", [
			"action"               => $action,
			"session_id"           => $session_id,
			"handle_id"            => $handle_id,
			"janus_error_code"     => $error_code,
			"janus_error_response" => $response,
		]);

		// выбрасываем исключение с полученной ошибкой и ответом от janus
		$error_text = $error_config[$error_code];
		throw new \paramException("{$error_text}. Janus response: {$response}");
	}
}
