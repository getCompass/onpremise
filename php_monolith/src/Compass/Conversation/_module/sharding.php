<?php /** @noinspection DuplicatedCode */

namespace Compass\Conversation;

use BaseFrame\Database\PDODriver\DebugMode;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;

/**
 * Класс шардинга для изоляции настроек подключения внутри модуля.
 * @package Compass\Conversation
 */
class ShardingGateway extends \ShardingGateway {

	protected static ?ShardingGateway $_instance = null;

	/**
	 * Инициализирует экземпляр работы с шардящимися подключениями.
	 *
	 * @return static
	 * @throws \parseException
	 */
	public static function instance():static {

		if (is_null(static::$_instance)) {

			static::$_instance = new ShardingGateway([
				\ShardingGateway::DB_KEY     => getConfig("SHARDING_MYSQL"),
				\ShardingGateway::BUS_KEY    => getConfig("SHARDING_RABBIT"),
				\ShardingGateway::CACHE_KEY  => getConfig("SHARDING_MCACHE"),
				\ShardingGateway::RPC_KEY    => getConfig("SHARDING_GO"),
				\ShardingGateway::SEARCH_KEY => getConfig("SHARDING_MANTICORE"),
			]);
		}

		return static::$_instance;
	}

	/**
	 * Возвращает класс для работы с шиной данных.
	 */
	public static function cache():\mCache {

		// получаем конфиг с базой данных
		return \CompassApp\Gateway\Memcached::configured(static::instance()->_config_list[static::CACHE_KEY]);
	}

	/**
	 * Возвращает класс для работы с шиной данных.
	 *
	 * @param string $bus
	 *
	 * @return \Rabbit
	 * @throws ParseFatalException
	 */
	public static function rabbit(string $bus = "bus"):\Rabbit {

		$rabbit = parent::rabbit();
		$rabbit->setPostfixQueue(COMPANY_ID % 10);
		return $rabbit;
	}

	/**
	 * Возвращает объект для работы с базой данных.
	 *
	 * @param string $database
	 * @return \BaseFrame\Database\PDODriver
	 * @throws \BaseFrame\Exception\Gateway\DBShardingNotFoundException
	 */
	public static function database(string $database):\BaseFrame\Database\PDODriver {

		// получаем конфиг с базой данных
		$conf = static::instance()->_config_list[static::DB_KEY];

		if (!isset($conf[$database])) {
			throw new DBShardingNotFoundException("database not found in sharding config");
		}

		$conn_conf  = new \BaseFrame\Database\Config\Connection(
			host: $conf[$database]["mysql"]["host"],
			db_name: $conf[$database]["db"],
			user: $conf[$database]["mysql"]["user"],
			password: $conf[$database]["mysql"]["pass"],
			ssl: $conf[$database]["mysql"]["ssl"] ?? false,
		);

		$query_conf = new \BaseFrame\Database\Config\Query(
			debug_mode: \BaseFrame\Database\PDODriver\DebugMode::NONE,
			hooks: getConfig("DBHOOK")
		);

		return \BaseFrame\Database\Provider::instance()->connect($conn_conf, $query_conf);
	}
}
