<?php

namespace Compass\FileBalancer;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Exception\Request\ParamException;

/**
 * класс для работы с конфигом нод
 * ---
 * ФОРМАТ КОНФИГА
 * ---
 *
 * [
 *	[
 *		"node_id"			=> 1,
 *		"node_url"			=> "https://file1.example-domain.ru/",
 *		"enabled_for_new"		=> 1,
 *		"allow_file_source"	=> [1],
 *	],
 *	[
 *		"node_id"			=> 2,
 *		"node_url"			=> "https://file2.example-domain.ru/",
 *		"enabled_for_new"		=> 1,
 *		"allow_file_source"	=> [2, 3, 5, 6, 7],
 *	],
 * ];
 *
 */
class Type_Node_Config {

	// проверяем что нода существует
	public static function isNodeExists(int $node_id):bool {

		// получаем конфиг
		$config = self::getConfig();

		// пытаемся найти ноду
		foreach ($config as $item) {

			if ($item["node_id"] == $node_id) {
				return true;
			}
		}

		return false;
	}

	/**
	 * получаем идентификатор ноды
	 *
	 * @param int $file_source
	 *
	 * @return int
	 * @throws ParamException
	 */
	public static function getNodeIdForUpload(int $file_source):int {

		// получаем список нод
		$available_node_list = self::_getAvailableNodeList($file_source);

		// проверяем, что для записи доступна хотя бы одна нода
		if (count($available_node_list) < 1) {

			Type_System_Admin::log("files", "Node is not available in config");
			throw new ParamException("Node is not available in config");
		}

		// определяем ноду по каким-то параметрам и отдаем её
		$temp      = array_rand($available_node_list, 1);
		$node_item = $available_node_list[$temp];

		return $node_item["node_id"];
	}

	// получаем список доступных нод из конфига
	protected static function _getAvailableNodeList(int $file_source):array {

		// получаем конфиг
		$config = self::getConfig();

		$available_node_list = [];

		// пробегаем и оставляем только подходящие ноды
		foreach ($config as $item) {

			// нода должна быть включена на запись
			if ($item["enabled_for_new"] != 1) {
				continue;
			}

			// нода должна поддерживать этот file_source
			$file_source_list = array_values($item["allow_file_source"]);
			if (!in_array($file_source, $file_source_list)) {
				continue;
			}

			$available_node_list[] = $item;
		}

		return $available_node_list;
	}

	// возвращает ссылку ноды по ее идентификатору
	public static function getNodeUrl(int $node_id):string {

		// получаем конфиг
		$config = self::getConfig();

		// пробегаемся и ищем ноду с нужным идентификатором
		foreach ($config as $item) {

			if ($item["node_id"] != $node_id) {
				continue;
			}

			return mb_substr($item["node_url"], -1) != "/" ? $item["node_url"] . "/" : $item["node_url"];
		}

		throw new ReturnFatalException("Node with node_id {$node_id} not found");
	}

	// возвращает входную точку ноды для сокет запросов
	public static function getSocketUrl(int $node_id):string {

		// получаем конфиг
		$config = self::getConfig();

		// пробегаемся и ищем ноду с нужным идентификатором
		foreach ($config as $item) {

			if ($item["node_id"] != $node_id) {
				continue;
			}

			return $item["socket_url"];
		}

		throw new ReturnFatalException("Node with node_id {$node_id} not found");
	}

	// проверяет, отключена ли нода на запись
	public static function isNodeEnabledForNew(int $node_id):bool {

		// получаем конфиг
		$config = self::getConfig();

		// пробегаемся и ищем ноду с нужным идентификатором
		foreach ($config as $item) {

			if ($item["node_id"] != $node_id) {
				continue;
			}

			return $item["enabled_for_new"] == 1;
		}

		throw new ReturnFatalException("Node with node_id {$node_id} not found");
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	// возвращает конфиг нод
	public static function getConfig():array {

		return getConfig("NODE_LIST");
	}

	/**
	 * проверяет конфиг на валидность
	 *
	 */
	public static function isValidConfig(string $config_hash):bool {

		// получаем конфиг с сервера
		$config = toJson(self::getConfig());

		// проверяем конфиг на валидность
		if ($config_hash != sha1($config)) {
			return false;
		}

		return true;
	}

	// проверяет, что передан валидный элемент конфига
	public static function isConfigItemCorrect(array $config_item):bool {

		// проверяем, что элемент конфига - массив
		if (!is_array($config_item)) {
			return false;
		}

		// проверяем ключи и тип значений в массиве
		if (!isset($config_item["node_id"]) || intval($config_item["node_id"]) < 1) {
			return false;
		}

		if (!isset($config_item["node_url"]) || !isset($config_item["type"])) {
			return false;
		}

		if (!isset($config_item["enabled_for_new"]) || !in_array(intval($config_item["enabled_for_new"]), [0, 1])) {
			return false;
		}

		if (!isset($config_item["allow_file_source"]) || !is_array($config_item["allow_file_source"])) {
			return false;
		}

		return true;
	}
}