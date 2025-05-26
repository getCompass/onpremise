<?php

namespace BaseFrame\Database\Config;

use BaseFrame\Database\PDODriver\DebugMode;

/**
 * Класс с параметрами конфигурации подключения к MySQL.
 */
class Query {

	/**
	 * Класс с параметрами конфигурации подключения к MySQL.
	 */
	function __construct(
		public DebugMode   $debug_mode = DebugMode::NONE,
		public array       $hooks = [],
	) {

	}
}
