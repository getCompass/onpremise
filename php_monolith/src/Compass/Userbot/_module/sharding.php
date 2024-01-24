<?php /** @noinspection DuplicatedCode */

namespace Compass\Userbot;

/**
 * Класс шардинга для изоляции настроек подключения внутри модуля.
 * @package Compass\Userbot
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
				ShardingGateway::DB_KEY     => getConfig("SHARDING_MYSQL"),
				ShardingGateway::BUS_KEY    => getConfig("SHARDING_RABBIT"),
				ShardingGateway::CACHE_KEY  => getConfig("SHARDING_MCACHE"),
				ShardingGateway::RPC_KEY    => getConfig("SHARDING_GO"),
				ShardingGateway::SEARCH_KEY => getConfig("SHARDING_MANTICORE"),
			]);
		}

		return static::$_instance;
	}
}
