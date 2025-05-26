<?php

namespace BaseFrame\Conf;

/**
 * Класс-обертка для работы с конфигами
 */
class ConfProvider {

	/**
	 * Закрываем конструктор.
	 */
	protected function __construct() {

	}

	/**
	 * получаем config_sharding_go
	 *
	 */
	public static function shardingGo():array {

		return ConfHandler::instance()->shardingGo();
	}

	/**
	 * получаем config_sharding_sphinx
	 *
	 */
	public static function shardingSphinx():array {

		return ConfHandler::instance()->shardingSphinx();
	}

	/**
	 * получаем config_sharding_rabbit
	 *
	 */
	public static function shardingRabbit():array {

		return ConfHandler::instance()->shardingRabbit();
	}

	/**
	 * получаем config_sharding_mysql
	 *
	 */
	public static function shardingMysql():array {

		return ConfHandler::instance()->shardingMysql();
	}

	/**
	 * получаем config_sharding_mcache
	 *
	 */
	public static function shardingMcache():array {

		return ConfHandler::instance()->shardingMcache();
	}

	/**
	 * получаем config_global_office_ip
	 *
	 */
	public static function allowIp():array {

		return ConfHandler::instance()->allowIp();
	}
}
