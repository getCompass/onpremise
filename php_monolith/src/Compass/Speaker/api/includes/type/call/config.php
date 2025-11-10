<?php

namespace Compass\Speaker;

use JetBrains\PhpStorm\Pure;

/**
 * Класс для работы с конфигом stun, turn, janus серверов через sharedMemory
 *
 * Конфиг дублируется в sharedMemory и файл config.json в /private/
 * Нужно это для того, чтобы в случае потери данных из sharedMemory...
 * ... модуль мог самостоятельно восстановить изначальное состояние
 *
 * Все взаимодействие с конфигом происходит через этот класс
 *
 * ---
 * ФОРМАТ КОНФИГА
 * ---
 *
 * [
 *
 *    "stun_server_list"      => [
 *            [
 *                  "stun_id"            => 1,
 *                  "host"            => "stun1.example.com",
 *                  "port"            => 19302,
 *                  "is_enabled"      => 1,
 *            ],_getMemberList
 *      ],
 *
 *      "turn_server_list"      => [
 *            [
 *                  "turn_id"            => 1,
 *                  "host"            => "example.com",
 *                  "port"            => 3478,
 *                  "is_protocol_tcp" => 0,
 *                  "is_protocol_udp" => 1,
 *                  "secret_key"      => "turn_server_secret",
 *                  "is_enabled"      => 1,
 *            ],
 *      ],
 *
 *      "janus_node_list"            => [
 *            [
 *                  "node_id"                  => 1,
 *                  "host"                  => "127.0.0.1",
 *                  "internal_ip"            => "127.0.0.1",
 *                  "janus_path"            => "/janus",
 *                  "api_secret"            => "janus_api_secret",
 *                  "admin_secret"            => "janus_admin_secret",
 *                  "load_index"            => 1,
 *                  "is_grouping"            => 1,
 *                  "is_enabled"            => 1,
 *                  "available_for_new"      => 1
 *            ],
 *      ],
 * ];
 *
 */
class Type_Call_Config {

	// индексы загруженности ноды !!! каждый последующий индекс хуже предыдущего
	protected const _PERFECT_NODE_INDEX = 1;
	protected const _MIDDLE_NODE_INDEX  = 2;
	protected const _BAD_NODE_INDEX     = 3;

	// список с индексами загруженности ноды !!! обязательно в порядке возрастания
	protected const _LOAD_INDEX_LIST = [
		self::_PERFECT_NODE_INDEX,
		self::_MIDDLE_NODE_INDEX,
		self::_BAD_NODE_INDEX,
	];

	// лимит полученных рандомных нод
	public const LIMIT_GET_RANDOM_NODES = 5;

	// путь к файлу с бэкапом конфига
	protected const _BACKUP_FILE_PATH = PATH_ROOT . "private/config.json";

	// обновляем конфиг с серверами
	public static function updateConfig(array $config):void {

		// обновляем резервное значение в config.json
		// в pretty print чтобы можно было менять прямо в файле
		file_put_contents(self::_BACKUP_FILE_PATH, json_encode($config, JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT));
	}

	// -------------------------------------------------------
	// STUN SERVER
	// -------------------------------------------------------

	// проверяет, что передан валидный элемент stun сервера
	#[Pure]
	public static function isStunItemCorrect(array $stun_item):bool {

		// проверяем, что элемент конфига - массив
		if (!is_array($stun_item)) {
			return false;
		}

		// проверяем stun_id
		if (!isset($stun_item["stun_id"]) || intval($stun_item["stun_id"]) < 1) {
			return false;
		}

		// проверяем host
		if (!isset($stun_item["host"]) || $stun_item["host"] == "") {
			return false;
		}

		// проверяем port
		if (!isset($stun_item["port"]) || intval($stun_item["port"]) < 1) {
			return false;
		}

		// проверяем флаг enabled
		if (!isset($stun_item["is_enabled"]) || !in_array(intval($stun_item["is_enabled"]), [0, 1])) {
			return false;
		}

		return true;
	}

	// подводим под формат
	public static function prepareStunServer(array $stun_server):array {

		return [
			"stun_id"    => (int) $stun_server["stun_id"],
			"host"       => (string) $stun_server["host"],
			"port"       => (int) $stun_server["port"],
			"is_enabled" => (int) $stun_server["is_enabled"],
		];
	}

	// -------------------------------------------------------
	// TURN SERVER
	// -------------------------------------------------------

	// получить turn_item по turn_id
	public static function getTurnById(int $turn_id):array {

		$output = false;
		foreach (getConfig("TURN_SERVER_LIST") as $item) {

			if ($item["turn_id"] == $turn_id) {

				$output = $item;
				break;
			}
		}

		if ($output === false) {
			throw new \parseException(__METHOD__ . ": turn with passed turn_id not found");
		}

		return $output;
	}

	// проверяет, что передан валидный элемент turn сервера
	// @long
	public static function isTurnItemCorrect(array $turn_item):bool {

		// проверяем, что элемент конфига - массив
		if (!is_array($turn_item)) {
			return false;
		}

		// проверяем turn_id
		if (!isset($turn_item["turn_id"]) || intval($turn_item["turn_id"]) < 1) {
			return false;
		}

		// проверяем host
		if (!isset($turn_item["host"]) || $turn_item["host"] == "") {
			return false;
		}

		// проверяем port
		if (!isset($turn_item["port"]) || intval($turn_item["port"]) < 1) {
			return false;
		}

		// проверяем tls_port
		if (!isset($turn_item["tls_port"]) || intval($turn_item["tls_port"]) < 1) {
			return false;
		}

		// проверяем secret_key
		if (!isset($turn_item["secret_key"])) {
			return false;
		}

		// проверяем флаг is_protocol_tcp
		if (!isset($turn_item["is_protocol_tcp"]) || !in_array(intval($turn_item["is_protocol_tcp"]), [0, 1])) {
			return false;
		}

		// проверяем флаг is_protocol_udp
		if (!isset($turn_item["is_protocol_udp"]) || !in_array(intval($turn_item["is_protocol_udp"]), [0, 1])) {
			return false;
		}

		// проверяем флаг is_protocol_tls
		if (!isset($turn_item["is_protocol_tls"]) || !in_array(intval($turn_item["is_protocol_tls"]), [0, 1])) {
			return false;
		}

		// проверяем флаг is_enabled
		if (!isset($turn_item["is_enabled"]) || !in_array(intval($turn_item["is_enabled"]), [0, 1])) {
			return false;
		}

		return true;
	}

	// подводим под формат
	public static function prepareTurnServer(array $turn_server):array {

		return [
			"turn_id"         => (int) $turn_server["turn_id"],
			"host"            => (string) $turn_server["host"],
			"port"            => (int) $turn_server["port"],
			"tls_port"        => (int) $turn_server["tls_port"],
			"secret_key"      => (string) $turn_server["secret_key"],
			"is_protocol_tcp" => (int) $turn_server["is_protocol_tcp"],
			"is_protocol_udp" => (int) $turn_server["is_protocol_udp"],
			"is_protocol_tls" => (int) $turn_server["is_protocol_tls"],
			"is_enabled"      => (int) $turn_server["is_enabled"],
		];
	}

	// -------------------------------------------------------
	// JANUS NODE
	// -------------------------------------------------------

	// получить janus ноду по идентификатору
	public static function getJanusById(int $node_id):array {

		$output = false;
		foreach (getConfig("JANUS_NODE_LIST") as $item) {

			if ($item["node_id"] == $node_id) {

				$output = $item;
				break;
			}
		}

		if ($output === false) {
			throw new \parseException(__METHOD__ . ": node with passed node_id not found");
		}

		return $output;
	}

	// получает список janus нод
	public static function getJanusList():array {

		return getConfig("JANUS_NODE_LIST");
	}

	// получаем список доступных для новых звонков нод
	public static function getJanusAvailableNodes(?array $config_node_list = null):array {

		if (is_null($config_node_list)) {
			$config_node_list = self::getJanusList();
		}

		// получаем только те ноды, что включены и доступны для новых звонков
		$node_list = [];
		foreach ($config_node_list as $v) {

			if ($v["available_for_new"] == 0 || $v["is_enabled"] == 0) {
				continue;
			}

			$node_list[] = self::_prepareNodeForPing($v);
		}

		// если список с доступными для новых звонков нодами пуст, то получаем список с включенными нодами
		if (count($node_list) == 0) {
			$node_list = self::_getEnabledNodes($config_node_list, self::_PERFECT_NODE_INDEX);
		}

		return $node_list;
	}

	// получаем включенные ноды
	protected static function _getEnabledNodes(array $config_node_list, int $load_index, array $best_node_list = []):array {

		foreach ($config_node_list as $v) {

			if ($v["is_enabled"] == 0 || $v["load_index"] != $load_index) {
				continue;
			}

			$best_node_list[] = self::_prepareNodeForPing($v);

			if (count($best_node_list) == self::LIMIT_GET_RANDOM_NODES) {
				return $best_node_list;
			}
		}

		// если не набрали лимит нод, то увеличиваем индекс загруженности и добавляем к набранным уже ноды похуже
		$load_index = $load_index + 1;
		if (in_array($load_index, self::_LOAD_INDEX_LIST)) {
			return self::_getEnabledNodes($config_node_list, $load_index, $best_node_list);
		}

		// если ни одной включенной ноды не найдено, то выбрасываем исключение
		self::_throwIfNotFoundEnabledNodes($best_node_list);

		return $best_node_list;
	}

	// приводим к формату ноду
	protected static function _prepareNodeForPing(array $node):array {

		$mobile_node_url = $node["host"];
		$protocol        = Type_Janus_Node::getProtocol($node["is_ssl"]);
		$node_url        = $protocol . $node["host"] . ":" . $node["port"];

		return [
			"node_id"    => (int) $node["node_id"],
			"ip_address" => (string) $mobile_node_url,
			"node_url"   => (string) $node_url,
		];
	}

	// если ни одной включенной ноды не найдено, то выбрасываем исключение
	protected static function _throwIfNotFoundEnabledNodes(array $node_list):void {

		if (count($node_list) == 0) {
			throw new \returnException("not found enabled nodes");
		}
	}

	// проверяет, что передан валидный элемент janus сервера
	// @long
	public static function isJanusItemCorrect(array $janus_item):bool {

		if (!isset($janus_item["node_id"]) || intval($janus_item["node_id"]) < 1) {
			return false;
		}

		if (!isset($janus_item["host"]) || $janus_item["host"] == "") {
			return false;
		}

		if (!isset($janus_item["port"]) || intval($janus_item["port"]) < 1) {
			return false;
		}

		if (!isset($janus_item["internal_ip"])) {
			return false;
		}

		if (!isset($janus_item["is_ssl"]) || !in_array(intval($janus_item["is_ssl"]), [0, 1])) {
			return false;
		}

		if (!isset($janus_item["api_secret"])) {
			return false;
		}

		if (!isset($janus_item["admin_secret"])) {
			return false;
		}

		if (!isset($janus_item["turn_rest_api_key"])) {
			return false;
		}

		if (!isset($janus_item["turn_list"]) || !is_array($janus_item["turn_list"])) {
			return false;
		}

		if (!isset($janus_item["janus_path"])) {
			return false;
		}

		if (!isset($janus_item["janus_admin_path"])) {
			return false;
		}

		if (!isset($janus_item["load_index"]) || !in_array(intval($janus_item["load_index"]), self::_LOAD_INDEX_LIST)) {
			return false;
		}

		if (!isset($janus_item["is_grouping"]) || !in_array(intval($janus_item["is_grouping"]), [0, 1])) {
			return false;
		}

		if (!isset($janus_item["is_enabled"]) || !in_array(intval($janus_item["is_enabled"]), [0, 1])) {
			return false;
		}

		if (!isset($janus_item["available_for_new"]) || !in_array(intval($janus_item["available_for_new"]), [0, 1])) {
			return false;
		}

		return true;
	}

	// подводим под формат
	public static function prepareJanusNode(array $janus_node):array {

		return [
			"node_id"           => (int) $janus_node["node_id"],
			"host"              => (string) $janus_node["host"],
			"internal_ip"       => (string) $janus_node["internal_ip"],
			"port"              => (int) $janus_node["port"],
			"is_ssl"            => (int) $janus_node["is_ssl"],
			"api_secret"        => (string) $janus_node["api_secret"],
			"admin_secret"      => (string) $janus_node["admin_secret"],
			"turn_rest_api_key" => (string) $janus_node["turn_rest_api_key"],
			"turn_list"         => (array) $janus_node["turn_list"],
			"janus_path"        => (string) $janus_node["janus_path"],
			"janus_admin_path"  => (string) $janus_node["janus_admin_path"],
			"load_index"        => (int) $janus_node["load_index"],
			"is_grouping"       => (int) $janus_node["is_grouping"],
			"is_enabled"        => (int) $janus_node["is_enabled"],
			"available_for_new" => (int) $janus_node["available_for_new"],
		];
	}

	// получить janus ноду по ее host адресу обращения
	public static function getJanusByHost(string $domain, string $host):array {

		foreach (getConfig("JANUS_NODE_LIST") as $item) {

			$server_name = $item["host"];

			// разбиваем домен по частям
			$tmp = explode(".", $server_name);

			// получаем последние два элемента и делаем строку
			$item["host"] = implode(".", array_slice($tmp, count($tmp) - 2));

			// ищем совпадения
			if ($server_name == $domain || $item["host"] == $domain || ($item["internal_ip"] != "" && $item["internal_ip"] == $host)) {

				Type_System_Admin::log("getJanusByHost", [$domain, $host, $item, toJson(SERVER_TAG_LIST)]);
				return $item;
			}
		}

		Type_System_Admin::log("getJanusByHostNotExist", [$domain, $host, toJson(SERVER_TAG_LIST)]);
		throw new cs_Janus_Node_Not_Exist();
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------
}