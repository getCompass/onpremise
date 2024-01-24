<?php

namespace Compass\Speaker;

/*
 * класс для работы с объектом ноды janus-gateway
 */

/**
 * @property Type_Janus_Api $Api
 */
class Type_Janus_Node {

	// идентификатор ноды
	public $node_id;

	// хост ноды
	public $host;

	// порт ноды
	public $port;

	// путь до janus api
	public $janus_path;

	// путь до janus admin api
	public $janus_admin_path;

	// флаг, сообщающий использует ли нода сертификат
	public $is_ssl;

	// api secret
	public $api_secret;

	// admin secret
	public $admin_secret;

	// для подгрузки через __get()
	private $_allow_magic = [
		"Api",
	];

	// magic methods
	// @mixed
	function __get($name) {

		if (!in_array($name, $this->_allow_magic)) {

			throw new \parseException("You are trying to get non-existed users property: {$name}. Check for allowed properties.");
		}
		$class       = __NAMESPACE__ . "\Type_Janus_{$name}";
		$this->$name = new $class($this);
		return $this->$name;
	}

	// конструктор
	function __construct(int $node_id) {

		// получаем информацию о ноде
		$janus_node_info = Type_Call_Config::getJanusById($node_id);

		$this->node_id          = $node_id;
		$this->host             = $janus_node_info["host"];
		$this->port             = $janus_node_info["port"];
		$this->is_ssl           = $janus_node_info["is_ssl"];
		$this->api_secret       = $janus_node_info["api_secret"];
		$this->admin_secret     = $janus_node_info["admin_secret"];
		$this->janus_path       = $janus_node_info["janus_path"];
		$this->janus_admin_path = $janus_node_info["janus_admin_path"];
	}

	// функция инициализирующая объект для работы с нодой janus-gateway
	public static function init(int $node_id):self {

		if (!isset($GLOBALS[__CLASS__])) {
			$GLOBALS[__CLASS__] = [];
		}

		if (isset($GLOBALS[__CLASS__][$node_id])) {
			return $GLOBALS[__CLASS__][$node_id];
		}

		$GLOBALS[__CLASS__][$node_id] = new self($node_id);
		return $GLOBALS[__CLASS__][$node_id];
	}

	// функция, определяющая оптимальную ноду janus_gateway
	public static function getOptimalNode(array $ping_result_1, array $ping_result_2, array $janus_config = []):int {

		// собираем результаты пинга нод пользователями
		$node_result = self::_getPingResult($ping_result_1, $ping_result_2);

		$optimal_node_id = self::_getOptimalNodeId($node_result, $janus_config);
		return $optimal_node_id;
	}

	// получаем результаты пинга нод
	protected static function _getPingResult(array $ping_result_1, array $ping_result_2):array {

		// собираем результаты пинга нод
		$ping_result_list = self::_getGeneralResult($ping_result_1, $ping_result_2);

		$node_result = [];
		foreach ($ping_result_list as $k => $v) {

			// если пинг конретной ноды есть у обоих пользователей, то собираем результаты
			if (count($v) == 2) {

				// сортируем результаты пинга ноды
				sort($v);

				// если внезапно получили пинг по одной из нод равный 0 ms, то считаем что до ноды не достучались,
				// потому пропускаем эту ноду
				if ($v[0] == 0) {
					continue;
				}

				$node_result[$k] = ($v[0] + $v[1]) * $v[1] / $v[0];
			}
		}
		return $node_result;
	}

	// получаем общий результат обоих пользователей
	protected static function _getGeneralResult(array $ping_result_1, array $ping_result_2):array {

		$ping_result_list = [];
		foreach ($ping_result_1 as $v) {
			$ping_result_list[$v["node_id"]][] = $v["latency"];
		}
		foreach ($ping_result_2 as $v) {
			$ping_result_list[$v["node_id"]][] = $v["latency"];
		}

		return $ping_result_list;
	}

	// получаем оптимальную ноду для звонков
	protected static function _getOptimalNodeId(array $result, array $janus_config):int {

		// если нет никаких доступных нод для нового звонка
		if (count($result) == 0) {
			return self::_getAvailableNodeId($janus_config);
		}

		// получаем лучшую ноду для звонка
		$optimal_node_id = array_search(min($result), $result);

		// проверяем, что нода доступна для звонков
		foreach ($janus_config as $v) {

			if ($optimal_node_id == $v["node_id"]) {

				if ($v["is_enabled"] == 1 && $v["available_for_new"] == 1) {
					return $optimal_node_id;
				}
				break;
			}
		}

		// если оптимальная нода недоступна, то удаляем из результата и пытаемся заново
		unset($result[$optimal_node_id]);
		return self::_getOptimalNodeId($result, $janus_config);
	}

	// получаем ноду, доступную для звонков
	protected static function _getAvailableNodeId(array $janus_config):int {

		// сортируем по индексу загруженности ноды
		$load_index = array_column($janus_config, "load_index");
		array_multisort($load_index, SORT_ASC, $janus_config);

		// проходимся по конфигу с янус нодами, если лучшая по загружена нода доступна,
		// то возвращаем её
		foreach ($janus_config as $v) {

			if ($v["is_enabled"] == 1 && $v["available_for_new"] == 1) {
				return $v["node_id"];
			}
		}

		throw new \returnException("not found available node for new call");
	}

	// функция для получения основного url до janus ноды без излишеств
	public function getUrl():string {

		$protocol = self::getProtocol($this->is_ssl);
		$url      = "{$protocol}{$this->host}";

		// нужно ли конкретизировать порт, например (если порт не равен 80 или 443 по умолчанию)
		if ($this->port != 80 && $this->port != 443) {

			$url .= ":{$this->port}";
		}

		// добавляем path
		$url .= "/{$this->janus_path}/";

		return $url;
	}

	// функция для получения event endpoint, куда будет стучаться пользователи
	public function getEventEndpoint(int $session_id):string {

		$url = $this->getUrl();

		// добавляем session_id
		$url .= "{$session_id}/";
		return $url;
	}

	// функция для получения handle endpoint, куда будет стучаться пользователи
	public function getHandleEndpoint(int $session_id, int $handle_id):string {

		$url = $this->getUrl();

		// добавляем session_id & handle_id
		$url .= "{$session_id}/{$handle_id}/";
		return $url;
	}

	// получаем протокол
	public static function getProtocol(int $is_ssl):string {

		// если нода использует сертификат
		if ($is_ssl == 1) {
			return "https://";
		}

		return "http://";
	}

	// функция для получения токена пользователя
	public static function getUserToken(int $session_id):string {

		return hash_hmac("sha1", $session_id, JANUS_USER_TOKEN_SECRET);
	}

	// функция для получения токена разговорной комнаты
	public static function getRoomToken(int $room_id, int $participant_id, int $publisher_user_id):string {

		return hash_hmac("sha1", $room_id . $participant_id . $publisher_user_id, JANUS_USER_TOKEN_SECRET);
	}

	// функция подготавливает объект janus_communication, содержащий все необходимые клиенту параметры для установления прямой коммуникации с janus сервером
	public function getJanusCommunicationSingle(array $publisher_row, array $subscriber_row):array {

		$janus_user_token    = self::getUserToken($publisher_row["session_id"]);
		$url                 = $this->getUrl();
		$event_endpoint      = $this->getEventEndpoint($publisher_row["session_id"]);
		$pub_handle_endpoint = $this->getHandleEndpoint($publisher_row["session_id"], $publisher_row["handle_id"]);
		$sub_handle_endpoint = $this->getHandleEndpoint($subscriber_row["session_id"], $subscriber_row["handle_id"]);
		$room_token          = self::getRoomToken($subscriber_row["room_id"], $subscriber_row["participant_id"], $subscriber_row["publisher_user_id"]);

		return [
			"session_id"          => $publisher_row["session_id"],
			"pub_handle_id"       => $publisher_row["handle_id"],
			"sub_handle_id"       => $subscriber_row["handle_id"],
			"token"               => $janus_user_token,
			"url"                 => $url,
			"event_endpoint"      => $event_endpoint,
			"pub_handle_endpoint" => $pub_handle_endpoint,
			"sub_handle_endpoint" => $sub_handle_endpoint,
			"publisher_user_id"   => $subscriber_row["publisher_user_id"],
			"room_id"             => $subscriber_row["room_id"],
			"room_token"          => $room_token,
			"participant_id"      => $subscriber_row["participant_id"],
		];
	}

	// функция подготавливает объект janus_communication, содержащий все необходимые клиенту параметры для установления прямой коммуникации с janus сервером
	public function getJanusCommunicationData(array $publisher_row, array $subscriber_list, array $opponents_media_data_list, bool $is_need_relay):array {

		// подготавливаем pub_connection_data
		$pub_connection_data = $this->getPubConnectionData($publisher_row, $is_need_relay);

		// подготавливаем sub_connection_data
		$sub_connection_data = $this->getSubConnectionDataList($subscriber_list, $opponents_media_data_list, $is_need_relay);

		// получаем токен
		$janus_user_token = self::getUserToken($publisher_row["session_id"]);

		// получаем event_endpoint
		$event_endpoint = $this->getEventEndpoint($publisher_row["session_id"]);

		return [
			"session_id"          => $publisher_row["session_id"],
			"event_endpoint"      => $event_endpoint,
			"pub_connection_data" => $pub_connection_data,
			"sub_connection_data" => $sub_connection_data,
			"token"               => $janus_user_token,
		];
	}

	// подготавливаем pub_connection_data
	public function getPubConnectionData(array $publisher_row, bool $is_need_relay):array {

		$handle_endpoint = $this->getHandleEndpoint($publisher_row["session_id"], $publisher_row["handle_id"]);
		$ice_server_data = $this->getIceServerData($publisher_row["user_id"], $is_need_relay);
		$token           = $this::getUserToken($publisher_row["session_id"]);
		return [
			"handle_id"       => (int) $publisher_row["handle_id"],
			"handle_endpoint" => (string) $handle_endpoint,
			"connection_uuid" => (string) $publisher_row["connection_uuid"],
			"ice_server_data" => $ice_server_data,
			"token"           => (string) $token,
		];
	}

	// подготавливаем sub_connection_data
	public function getSubConnectionDataList(array $subscriber_list, array $opponents_media_data_list, bool $is_need_relay):array {

		$output = [];
		foreach ($subscriber_list as $subscriber_row) {

			$opponent_media_data = $opponents_media_data_list[$subscriber_row["publisher_user_id"]];
			$output[]            = $this->getSubConnectionData($subscriber_row, $opponent_media_data, $is_need_relay);
		}

		return $output;
	}

	// подготавливаем sub_connection_data
	public function getSubConnectionData(array $subscriber_row, array $opponent_media_data, bool $is_need_relay):array {

		$handle_endpoint = $this->getHandleEndpoint($subscriber_row["session_id"], $subscriber_row["handle_id"]);
		$token           = $this::getUserToken($subscriber_row["session_id"]);
		$ice_server_data = $this->getIceServerData($subscriber_row["user_id"], $is_need_relay);
		return [
			"handle_id"         => (int) $subscriber_row["handle_id"],
			"handle_endpoint"   => (string) $handle_endpoint,
			"publisher_user_id" => (int) $subscriber_row["publisher_user_id"],
			"room_id"           => (int) $subscriber_row["room_id"],
			"participant_id"    => (int) $subscriber_row["participant_id"],
			"connection_uuid"   => (string) $subscriber_row["connection_uuid"],
			"is_enabled_audio"  => (int) $opponent_media_data["is_enabled_audio"] ? 1 : 0,
			"is_enabled_video"  => (int) $opponent_media_data["is_enabled_video"] ? 1 : 0,
			"ice_server_data"   => $ice_server_data,
			"token"             => (string) $token,
		];
	}

	// функция подготавливает объект ice_server_data, содержащий все неободимые клиенту параметры для создания peer_connection соединения
	public function getIceServerData(int $user_id, bool $is_need_relay):array {

		// по умолчанию стараемся всегда соединять пользователя напрямую с Janus сервером по UDP
		$ice_transport_policy = Type_TurnServer_Session::ICE_TRANSPORT_ALL;

		// получаем список TURN серверов
		$turn_list = Type_TurnServer_Session::chooseServerForClient($user_id, $this->node_id);

		// если пользователя нужно принудительно пустить через relay (TURN сервер) и они имеются
		if ($is_need_relay && count($turn_list) > 0) {
			$ice_transport_policy = Type_TurnServer_Session::ICE_TRANSPORT_RELAY;
		}

		return [
			"turn_list"            => $turn_list,
			"stun_list"            => Type_StunServer_Main::getList(),
			"ice_transport_policy" => $ice_transport_policy,
		];
	}
}
