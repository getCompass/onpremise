<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс для работы с jitsi нодой
 * @package Compass\Jitsi
 */
class Domain_Jitsi_Entity_Node {

	/** @var string ключ для получения списка jitsi нод */
	protected const _CONFIG_NODE_LIST_KEY = "JITSI_NODE_LIST";

	/**
	 * получаем конфиг ноды
	 *
	 * @return Struct_Jitsi_Node_Config
	 * @throws Domain_Jitsi_Exception_Node_NotFound
	 */
	public static function getConfig(string $domain):Struct_Jitsi_Node_Config {

		$node_list = self::_loadConfigNodeList();
		foreach ($node_list as $node) {

			if ($node->domain === $domain) {
				return $node;
			}
		}

		throw new Domain_Jitsi_Exception_Node_NotFound();
	}

	/**
	 * подгружаем конфиги с jitsi нодами
	 *
	 * @return Struct_Jitsi_Node_Config[]
	 */
	protected static function _loadConfigNodeList():array {

		$node_list = getConfig(self::_CONFIG_NODE_LIST_KEY);
		return array_map(static fn(array $row) => new Struct_Jitsi_Node_Config(...$row), $node_list);
	}

	/**
	 * выбриаем случайную ноду
	 *
	 * @return Struct_Jitsi_Node_Config
	 * @throws ParseFatalException
	 */
	public static function getRandomNode():Struct_Jitsi_Node_Config {

		$node_list = self::_loadConfigNodeList();
		if (count($node_list) == 0) {
			throw new ParseFatalException("no one jitsi node exists");
		}

		return $node_list[array_rand($node_list)];
	}
}