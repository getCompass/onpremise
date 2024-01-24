<?php

namespace Compass\Speaker;

/*
 * класс генерирует авторизовачные данные для рабочей сессии с TURN сервером
 * генерирует как для клиентских соединений, так и для соединений Janus ноды
 */

class Type_TurnServer_Session {

	// время жизни авторизовачных данных в секундах
	// после чего они становятся некорректными
	protected const _SESSION_TTL = DAY1;

	// -------------------------------------------------------
	// список значений для транспортной политики ICE
	// -------------------------------------------------------

	public const ICE_TRANSPORT_RELAY = "relay";
	public const ICE_TRANSPORT_ALL   = "all";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// функция возвращает tls turn сервер закрепленный за определенной Janus нодой для обслуживания соединений пользователя
	// возвращает массив следующего вида:
	// [
	//		"urls"		=> [
	//			"turns: 5.188.42.210:443?transport=tcp",
	//		],
	//		"username"		=> "1558066310:uid160605",
	//		"credential"	=> "1Dj9XZ5fwvKS6YoQZOoORcFnXaI=",
	// ]
	public static function chooseTlsServerForClient(int $user_id, int $janus_node_id, array $turn_list = null):array {

		// получаем список включенных TURN серверов из того списка, которые закреплены за janus нодой
		if (is_null($turn_list)) {
			$turn_list = self::_getTurnListByJanusNodeId($janus_node_id);
		}

		$enabled_turn_list = self::_getEnabledTurnList($turn_list);

		// если вернулся пустой список — значит turn серверов нет — возвращаем пустой массив
		if (count($enabled_turn_list) == 0) {
			return [];
		}

		// выбираем оптимальный сервер
		$turn_server = self::_chooseServer($enabled_turn_list);

		// собираем ответ
		$output = self::_prepareClientFormatTls($user_id, $turn_server);
		return $output;
	}

	// функция формирует ответ с tls turn сервером
	protected static function _prepareClientFormatTls(int $user_id, array $turn_server_item):array {

		// генерируем авторизовачные данные
		$auth_data = self::_genAuthParams("uid{$user_id}", $turn_server_item["secret_key"]);

		// собираем url адреса с используемыми протоколами
		$urls = [];
		if ($turn_server_item["is_protocol_tls"] == 1) {

			$urls[] = "turns:{$turn_server_item["host"]}:{$turn_server_item["tls_port"]}?transport=tcp";
		}

		if (count($urls) == 0) {
			throw new \parseException("not one used protocol");
		}

		return self::_clientFormat($urls, $auth_data);
	}

	// функция возвращает turn сервер закрепленный за определенной Janus нодой для обслуживания соединений пользователя
	// возвращает массив следующего вида:
	// [
	//		"urls"		=> [
	//			"turn: 5.188.42.210:80?transport=udp",
	//			"turn: 5.188.42.210:80?transport=tcp",
	//			"turns: 5.188.42.210:443?transport=tcp",
	//		],
	//		"username"		=> "1558066310:uid160605",
	//		"credential"	=> "1Dj9XZ5fwvKS6YoQZOoORcFnXaI=",
	// ]
	public static function chooseServerForClient(int $user_id, int $janus_node_id, array $turn_list = null):array {

		// получаем список включенных TURN серверов из того списка, которые закреплены за janus нодой
		if (is_null($turn_list)) {
			$turn_list = self::_getTurnListByJanusNodeId($janus_node_id);
		}

		$enabled_turn_list = self::_getEnabledTurnList($turn_list);

		// если вернулся пустой список — значит turn серверов нет — возвращаем пустой массив
		if (count($enabled_turn_list) == 0) {
			return [];
		}

		// выбираем оптимальный сервер
		$turn_server = self::_chooseServer($enabled_turn_list);

		// собираем ответ
		$output = self::_prepareClientFormat($user_id, $turn_server);
		return $output;
	}

	// функция формирует ответ со списком turn серверов
	protected static function _prepareClientFormat(int $user_id, array $turn_server_item):array {

		// генерируем авторизовачные данные
		$auth_data = self::_genAuthParams("uid{$user_id}", $turn_server_item["secret_key"]);

		// собираем url адреса с используемыми протоколами
		$urls = [];
		if ($turn_server_item["is_protocol_udp"] == 1) {

			$urls[] = "turn:{$turn_server_item["host"]}:{$turn_server_item["port"]}?transport=udp";
		}
		if ($turn_server_item["is_protocol_tcp"] == 1) {

			$urls[] = "turn:{$turn_server_item["host"]}:{$turn_server_item["port"]}?transport=tcp";
		}

		if ($turn_server_item["is_protocol_tls"] == 1) {

			$urls[] = "turns:{$turn_server_item["host"]}:{$turn_server_item["tls_port"]}?transport=tcp";
		}

		if (count($urls) == 0) {
			throw new \parseException("not one used protocol");
		}

		return self::_clientFormat($urls, $auth_data);
	}

	// функция возвращает turn сервер закрепленный за определенной Janus нодой для обслуживания соединений Janus ноды
	// возвращает ответ следующего вида:
	// {
	//	"username": "12334939:mbzrxpgjys",
	//	"password": "adfsaflsjfldssia",
	//	"ttl": 86400,
	//	"uris": [
	//		"turn:1.2.3.4:9991?transport=udp",
	//		"turn:1.2.3.4:9992?transport=tcp",
	//		"turns:1.2.3.4:443?transport=tcp"
	//	]
	// }
	public static function chooseServerForJanus(int $janus_node_id, array $turn_list = null):array {

		// получаем список всех turn серверов, которые закреплены за Janus нодой
		if (is_null($turn_list)) {
			$turn_list = self::_getTurnListByJanusNodeId($janus_node_id);
		}

		$enabled_turn_list = self::_getEnabledTurnList($turn_list);

		// если вернулся пустой список — значит turn серверов нет — возвращаем пустой массив
		if (count($enabled_turn_list) == 0) {
			return [];
		}

		// выбираем оптимальный сервер
		$turn_server = self::_chooseServer($enabled_turn_list);

		// собираем ответ
		$output = self::_prepareJanusFormat($turn_server);
		return $output;
	}

	// функция формирует ответ со списком turn серверов
	protected static function _prepareJanusFormat(array $turn_server_item):array {

		// генерируем авторизовачные данные, как просит janus
		$auth_data             = self::_genAuthParams("janus", $turn_server_item["secret_key"]);
		$auth_data["password"] = $auth_data["credential"];
		$auth_data["ttl"]      = self::_SESSION_TTL;
		unset($auth_data["credential"]);

		// собираем url адреса с используемыми протоколами
		$urls = [];
		if ($turn_server_item["is_protocol_udp"] == 1) {

			$urls[] = "turn:{$turn_server_item["host"]}:{$turn_server_item["port"]}?transport=udp";
		}
		if ($turn_server_item["is_protocol_tcp"] == 1) {

			$urls[] = "turn:{$turn_server_item["host"]}:{$turn_server_item["port"]}?transport=tcp";
		}
		if (count($urls) == 0) {
			throw new \parseException("not one used protocol");
		}
		if ($turn_server_item["is_protocol_tls"] == 1) {

			$urls[] = "turns:{$turn_server_item["host"]}:{$turn_server_item["tls_port"]}?transport=tcp";
		}

		return self::_janusFormat($urls, $auth_data);
	}

	// собираем ответ
	protected static function _janusFormat(array $urls, array $auth_data):array {

		$temp = $auth_data;

		// пробегаемся по всем адресам turn-сервера
		foreach ($urls as $v) {
			$temp["uris"][] = $v;
		}

		return $temp;
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получить turn_list привязанных к Janus ноде
	protected static function _getTurnListByJanusNodeId(int $janus_node_id):array {

		$janus_item = Type_Call_Config::getJanusById($janus_node_id);

		// получаем список с объектами turn серверов
		$turn_list = [];
		foreach ($janus_item["turn_list"] as $turn_id) {
			$turn_list[] = Type_Call_Config::getTurnById($turn_id);
		}

		return $turn_list;
	}

	// метод для получения списка только с включенными turn-серверами
	protected static function _getEnabledTurnList(array $turn_server_list):array {

		$enabled_turn_list = [];
		foreach ($turn_server_list as $v) {

			if ($v["is_enabled"] == 0) {
				continue;
			}

			$enabled_turn_list[] = $v;
		}

		return $enabled_turn_list;
	}

	// функция определяет turn сервер
	protected static function _chooseServer(array $turn_server_list):array {

		return $turn_server_list[random_int(0, count($turn_server_list) - 1)];
	}

	// функция генерирует username и пароль для будущей авторизации сессии на turnserver
	// возвращает массив следующего вида:
	// [
	//	"username"	 => "1558066310:$username_suffix",
	//	"credential" => "1Dj9XZ5fwvKS6YoQZOoORcFnXaI="
	// ]
	// @mixed
	protected static function _genAuthParams($username_suffix, string $turn_server_secret):array {

		$time = time() + self::_SESSION_TTL;

		$username = "{$time}:{$username_suffix}";
		$output   = [
			"username"   => $username,
			"credential" => base64_encode(hash_hmac("sha1", $username, $turn_server_secret, true)),
		];

		return $output;
	}

	// собираем ответ
	protected static function _clientFormat(array $urls, array $auth_data):array {

		$temp = $auth_data;

		// пробегаемся по всем адресам turn-сервера
		foreach ($urls as $v) {
			$temp["urls"][] = $v;
		}

		$output[] = $temp;

		return $output;
	}
}