<?php

namespace Compass\Janus;

// класс обрабатывающий все входящие запросы
use BaseFrame\Exception\Request\ParamException;

/**
 * Класс обработки входящих запросов на /janus точку входа.
 */
class Janus_Handler implements \RouteHandler {

	/**
	 * @inheritDoc
	 */
	public function getServedRoutes():array {

		return ["get", "post"];
	}

	/**
	 * @inheritDoc
	 */
	public function getType():string {

		return "janus";
	}

	/**
	 * @inheritDoc
	 */
	public function __toString():string {

		return static::class;
	}

	/**
	 * @inheritDoc
	 */
	public function handle(string $route, array $post_data):array {

		$result = $route === "post" ? static::doHandlePost($post_data) : self::doHandleGet();
		return ["result" => $result];
	}

	/**
	 * Обрабатываем GET.
	 */
	public static function doHandleGet():string {

		// если переданный токен некорректен
		self::_throwIfIncorrectPostToken();

		// устанавливаем все опции и осуществляем запрос
		$curl = self::_getCurl();
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($curl, CURLOPT_URL, "http://" . JANUS_HOST . ":" . JANUS_PORT . $_SERVER["REQUEST_URI"]);
		$response = curl_exec($curl);

		// если запрос не удался
		if ($response === false) {

			// матчим запрос вида /janus/$session_id/ если вдруг клиент дернул при завершении звонка, когда удалили сессию
			// передан корректный токен и клиент обращается к своей сессии
			if (preg_match("/janus\/[0-9]+/", $_SERVER["REQUEST_URI"])) {
				return json_encode((object) ["janus" => "keepalive"]);
			}

			$curl_error = curl_error($curl);
			throw new \returnException($curl_error);
		}

		return $response;
	}

	/**
	 * Обрабатываем POST.
	 */
	public static function doHandlePost(array $post_data):string {

		// получаем переданные данные
		$request_data = file_get_contents("php://input");
		$data         = json_decode($request_data, true, 512, JSON_BIGINT_AS_STRING);

		if (is_null($data)) {
			$data = $post_data;
		}

		// если запрос от пользователя
		if (self::_isClientRequest($data)) {

			// если переданный токен некорректен
			self::_throwIfIncorrectPostToken($data);

			// обновляем данные пользовательского запроса
			$data = self::_doUpgradeClientRequest($data);
		}

		return self::_doProxyPost($data);
	}

	/**
	 * Проверяем, является ли запрос пользовательским.
	 */
	protected static function _isClientRequest(array $data):bool {

		if (self::_isTrickleRequest($data)) {
			return true;
		}
		if (self::_isPublishRequest($data)) {
			return true;
		}
		if (self::_isStartRequest($data)) {
			return true;
		}
		if (self::_isConfigureRequest($data)) {
			return true;
		}
		if (self::_isJoinSubscriberRequest($data)) {
			return true;
		}

		return false;
	}

	/**
	 * Пришел запрос trickle (прислали кандидат)?
	 */
	protected static function _isTrickleRequest(array $data):bool {

		return isset($data["janus"]) && $data["janus"] === "trickle";
	}

	/**
	 * Пришел запрос publish?
	 */
	protected static function _isPublishRequest(array $data):bool {

		return isset($data["body"]["request"]) && $data["body"]["request"] === "publish";
	}

	/**
	 * Пришел запрос start?
	 */
	protected static function _isStartRequest(array $data):bool {

		return isset($data["body"]["request"]) && $data["body"]["request"] === "start";
	}

	/**
	 * Пришел запрос configure?
	 */
	protected static function _isConfigureRequest(array $data):bool {

		return isset($data["body"]["request"]) && $data["body"]["request"] === "configure";
	}

	/**
	 * Пришел запрос.
	 */
	protected static function _isJoinSubscriberRequest(array $data):bool {

		return isset($data["body"]["request"])
			&& isset($data["body"]["ptype"])
			&& $data["body"]["request"] === "join"
			&& $data["body"]["ptype"] === "subscriber";
	}

	/**
	 * Обновляем данные пользовательского запроса.
	 */
	protected static function _doUpgradeClientRequest(array $data):array {

		// убираем токен пользователя, добавляем apisecret, без которого janus будет ругаться на запрос
		unset($data["token"]);
		$data["apisecret"] = JANUS_API_SECRET;

		// если publish запрос, то выставляем флаги audio & video издателя на false
		if (self::_isPublishRequest($data)) {

			$data["request"]["audio"] = false;
			$data["request"]["video"] = false;
		}

		return $data;
	}

	/**
	 * Проксируем post запрос
	 */
	protected static function _doProxyPost(array $data):string {

		$curl = self::_getCurl();

		// готовим данные запроса
		$ar_post = json_encode($data);

		// устанавливаем запрос к Janus
		curl_setopt($curl, CURLOPT_POSTFIELDS, $ar_post);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($curl, CURLOPT_URL, "http://" . JANUS_HOST . ":" . JANUS_PORT . $_SERVER["REQUEST_URI"]);

		// совершаем запрос
		$response = curl_exec($curl);

		// если запрос не удался
		if ($response === false) {

			$curl_error = curl_error($curl);
			throw new \returnException($curl_error);
		}

		return $response;
	}

	/**
	 * Выдаем exception, если переданный токен некорректен
	 */
	protected static function _throwIfIncorrectPostToken(array $post_data = []):void {

		$post_user_token = self::_getUserTokenFromPostData($post_data);
		$post_room_token = self::_getRoomTokenFromPostData($post_data);

		// если токен в итоге не найден, то выдаем исключение
		if ($post_user_token === false && $post_room_token === false) {
			throw new ParamException("incorrect token");
		}

		// сравниваем токен, присланный пользователем, чтобы убедиться что запрос подписан верно
		$janus_user_token_hash = self::_getJanusUserTokenHash();
		if ($post_user_token != $janus_user_token_hash) {
			throw new ParamException("incorrect token");
		}

		// если передан токен для вступления в комнату, то проверяем на корректность, иначе - заканчиваем выполнение
		if ($post_room_token === false) {
			return;
		}

		$janus_room_token_hash = self::_getJanusRoomTokenHash($post_data);
		if ($post_room_token !== $janus_room_token_hash) {
			throw new paramException("incorrect token");
		}
	}

	/**
	 * Получаем токен пользователя, которым тот должен подписывать каждый свой запрос к Janus.
	 */
	protected static function _getUserTokenFromPostData(array $post_data):string|false {

		$token = false;

		// получаем токен, если:
		// - он имеется в заголовках
		// - он передан в get-запросах
		// - он имеется в post-данных
		$headers = getallheaders();

		if (isset($headers["Token"])) {
			$token = $headers["Token"];
		}

		if (isset($_GET["token"])) {
			$token = $_GET["token"];
		}

		if (isset($post_data["token"])) {
			$token = $post_data["token"];
		}

		return $token;
	}

	/**
	 * Получаем токен для комнаты, которым тот должен подписывать запрос присоединения к комнате.
	 */
	protected static function _getRoomTokenFromPostData(array $post_data):false|string {

		$token = false;

		// получаем токен, если:
		// - он имеется в заголовках
		// - он передан в get-запросах
		// - он имеется в post-данных
		$headers = getallheaders();
		if (isset($headers["Room-Token"])) {
			$token = $headers["Room-Token"];
		}
		if (isset($_GET["room_token"])) {
			$token = $_GET["room_token"];
		}
		if (isset($post_data["room_token"])) {
			$token = $post_data["room_token"];
		}

		return $token;
	}

	/**
	 * Получаем токен для запроса действия над комнатой.
	 */
	protected static function _getJanusRoomTokenHash(array $post_data):string {

		$room_id           = self::_getRoomId($post_data);
		$participant_id    = self::_getParticipantId($post_data);
		$publisher_user_id = self::_getPublisherUserId($post_data);

		// получаем токен на основе секрета & данных пользователя
		$janus_room_token_hash = hash_hmac("sha1", $room_id . $participant_id . $publisher_user_id, JANUS_USER_TOKEN_SECRET);

		return $janus_room_token_hash;
	}

	/**
	 * Получаем id разговорной комнаты.
	 */
	protected static function _getRoomId(array $post_data):int {

		return $post_data["body"]["room"];
	}

	/**
	 * Получаем participant_id.
	 */
	protected static function _getParticipantId(array $post_data):int {

		return $post_data["body"]["id"];
	}

	/**
	 * Получаем id паблишера, на которого хотим подписать саба.
	 */
	protected static function _getPublisherUserId(array $post_data):int {

		return $post_data["body"]["feed"];
	}

	/**
	 * Получаем пользовательский токен.
	 */
	protected static function _getJanusUserTokenHash():string {

		$session_id = self::_getSessionId();

		// получаем токен на основе секрета & session_id пользователя
		$janus_user_token_hash = hash_hmac("sha1", $session_id, JANUS_USER_TOKEN_SECRET);

		return $janus_user_token_hash;
	}

	/**
	 * Получаем $session_id из ссылок вида:
	 *
	 * – example.com/path_to_janus/$session_id/$handle_id
	 * – example.com/path_to_janus/$session_id
	 */
	protected static function _getSessionId():int {

		$row = $_SERVER["REQUEST_URI"];

		// если есть дополнительные get-параметры, то убираем их
		$str = strpos($row, "?");
		if ($str) {
			$row = substr($row, 0, $str);
		}

		// разбиваем путь, откуда поступил запрос
		$temp = explode("/", trim($row, "/"));

		// переворачиваем массив
		$temp = array_reverse($temp);

		// если элемент с индексом 1 – это цифра, то это session_id из ссылки вида example.com/path_to_janus/$session_id/$handle_id
		if (ctype_digit($temp[1])) {
			return $temp[1];
		}

		// иначе должен быть элемент с индексом 0
		return $temp[0];
	}

	/**
	 * Получаем объект curl.
	 */
	protected static function _getCurl():\CurlHandle|false {

		// инициируем curl
		$curl = curl_init();

		// задаем загаловки
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			"Content-type: application/json",
		]);

		// задаем опции
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		return $curl;
	}
}