<?php

namespace BaseFrame\Conf;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для работы с конфигами
 */
class ConfHandler {

	private static ConfHandler|null $_instance = null;
	private array                   $_config_sharding_go;
	private array                   $_config_sharding_sphinx;
	private array                   $_config_sharding_rabbit;
	private array                   $_config_sharding_mysql;
	private array                   $_config_sharding_mcache;
	private array                   $_config_allow_ip;

	/**
	 * Conf constructor.
	 *
	 * @throws ReturnFatalException
	 */
	private function __construct(array $config_sharding_go, array $config_sharding_sphinx, array $config_sharding_rabbit,
					     array $config_sharding_mysql, array $config_sharding_mcache, array $config_allow_ip) {

		$this->_config_sharding_go     = $config_sharding_go;
		$this->_config_sharding_sphinx = $config_sharding_sphinx;
		$this->_config_sharding_rabbit = $config_sharding_rabbit;
		$this->_config_sharding_mysql  = $config_sharding_mysql;
		$this->_config_sharding_mcache = $config_sharding_mcache;
		$this->_config_allow_ip        = $config_allow_ip;
	}

	/**
	 * инициализируем синглтон
	 *
	 */
	public static function init(array $config_sharding_go, array $config_sharding_sphinx, array $config_sharding_rabbit,
					    array $config_sharding_mysql, array $config_sharding_mcache, array $config_allow_ip):static {

		if (!is_null(static::$_instance)) {

			// подмержим конифг, чтобы оно не падало в монолите
			static::$_instance->_config_sharding_go = array_merge($config_sharding_go, static::$_instance->_config_sharding_go);
			return static::$_instance;
		}

		return static::$_instance = new static(
			$config_sharding_go, $config_sharding_sphinx, $config_sharding_rabbit,
			$config_sharding_mysql, $config_sharding_mcache, $config_allow_ip
		);
	}

	/**
	 * Возвращает экземпляр класса.
	 */
	public static function instance():static {

		if (is_null(static::$_instance)) {
			throw new ReturnFatalException("need to initialized before using");
		}

		return static::$_instance;
	}

	/**
	 * получаем config_sharding_go
	 *
	 */
	public function shardingGo():array {

		return $this->_config_sharding_go;
	}

	/**
	 * получаем config_sharding_sphinx
	 *
	 */
	public function shardingSphinx():array {

		return $this->_config_sharding_sphinx;
	}

	/**
	 * получаем config_sharding_rabbit
	 *
	 */
	public function shardingRabbit():array {

		return $this->_config_sharding_rabbit;
	}

	/**
	 * получаем config_sharding_mysql
	 *
	 */
	public function shardingMysql():array {

		return $this->_config_sharding_mysql;
	}

	/**
	 * получаем config_sharding_mcache
	 *
	 */
	public function shardingMcache():array {

		return $this->_config_sharding_mcache;
	}

	/**
	 * получаем config_allow_ip
	 *
	 */
	public function allowIp():array {

		return $this->_config_allow_ip;
	}
}
