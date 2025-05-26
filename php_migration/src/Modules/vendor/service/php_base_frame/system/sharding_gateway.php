<?php declare(strict_types=1);

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;

/**
 * Класс шардинга
 */
abstract class ShardingGateway {

	public const DB_KEY     = "db";
	public const BUS_KEY    = "bus";
	public const CACHE_KEY  = "cache";
	public const RPC_KEY    = "rpc";
	public const SEARCH_KEY = "search";

	protected const _KNOWN_CONFIG_NAME_LIST = [
		self::DB_KEY,
		self::BUS_KEY,
		self::CACHE_KEY,
		self::RPC_KEY,
		self::SEARCH_KEY,
	];

	/** @var array[] конфигурирующие функции */
	protected array $_config_list = [];

	/**
	 * Закрытый конструктор.
	 *
	 * @param array $config_list
	 *
	 * @throws ParseFatalException
	 */
	protected function __construct(array $config_list) {

		foreach ($config_list as $key => $cfg) {

			if (!in_array($key, static::_KNOWN_CONFIG_NAME_LIST)) {
				throw new ParseFatalException("passed unknown config {$key}");
			}

			$this->_config_list[$key] = $cfg;
		}

		if (count($this->_config_list) !== count(static::_KNOWN_CONFIG_NAME_LIST)) {
			throw new ParseFatalException("sharding config list is invalid");
		}
	}

	/**
	 * Статический конструктор.
	 * @return mixed
	 */
	public static function instance():mixed {

		return [];
	}

	/**
	 * Возвращает класс для работы с базой данных
	 * @throws \BaseFrame\Exception\Gateway\DBShardingNotFoundException
	 */
	public static function database(string $database):\BaseFrame\Database\PDODriver {

		// получаем конфиг с базой данных
		$conf = static::instance()->_config_list[static::DB_KEY];

		if (!isset($conf[$database])) {
			throw new DBShardingNotFoundException("database not found in sharding config");
		}

		return sharding::configuredPDO($conf[$database]);
	}

	/**
	 * Возвращает класс для работы с шиной данных.
	 *
	 * @param string $bus
	 *
	 * @return Rabbit
	 * @throws ParseFatalException
	 */
	public static function rabbit(string $bus = "bus"):Rabbit {

		// получаем конфиг с шиной
		$conf = static::instance()->_config_list[static::BUS_KEY];

		if (!isset($conf[$bus])) {
			throw new ParseFatalException("bus not found in sharding config");
		}

		return sharding::configuredRabbit($conf[$bus], $bus);
	}

	/**
	 * Возвращает класс для работы с шиной данных.
	 *
	 * @return mCache
	 */
	public static function cache():mCache {

		// получаем конфиг с базой данных
		return mCache::configured(static::instance()->_config_list[static::CACHE_KEY]);
	}

	/**
	 * Возвращает класс для работы с шиной данных.
	 *
	 * @param string $module
	 * @param string $class_name
	 * @param string $key
	 *
	 * @return Grpc
	 * @throws ParseFatalException
	 */
	public static function rpc(string $module, string $class_name, string $key = ""):Grpc {

		// получаем конфиг с шиной
		$conf = static::instance()->_config_list[static::RPC_KEY];

		if (!isset($conf[$module])) {
			throw new ParseFatalException("rpc not found in sharding config");
		}

		// получаем конфиг с базой данных
		return Bus::configured($conf[$module], $class_name, $key);
	}

	/**
	 * Возвращает класс для работы с поисковым движком.
	 *
	 * @param string $key
	 * @return \BaseFrame\Search\Manticore
	 */
	public static function search(string $key = ""):\BaseFrame\Search\Manticore {

		// получаем конфиг с шиной
		$conf = static::instance()->_config_list[static::SEARCH_KEY];

		// получаем конфиг с базой данных
		return \BaseFrame\Search\Provider::instance()->connect(new \BaseFrame\Search\Config\Connection(...$conf), $key);
	}
}
