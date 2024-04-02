<?php /** @noinspection DuplicatedCode */

declare(strict_types=1);

/**
 * Файл модуля.
 * По своей сути обертка для всех возможно шлюзов, используемых в модуле.
 *
 * Основная задача класса — обеспечивать единый доступ ко всем подключениям
 * и предоставлять возможность отключиться ото всюду разом.
 *
 * @package Compass\Federation
 */

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс шардинга для изоляции настроек подключения внутри модуля.
 * @package Compass\Federation
 */
class ShardingGateway extends \ShardingGateway {

	protected static ?ShardingGateway $_instance = null;

	/**
	 * Инициализирует экземпляр работы с шардящимися подключениями.
	 * @throws ParseFatalException
	 */
	public static function instance():static {

		if (is_null(static::$_instance)) {

			static::$_instance = new ShardingGateway([
				ShardingGateway::DB_KEY     => getConfig("SHARDING_MYSQL"),
				ShardingGateway::BUS_KEY    => getConfig("SHARDING_RABBIT"),
				ShardingGateway::CACHE_KEY  => getConfig("SHARDING_MCACHE"),
				ShardingGateway::RPC_KEY    => getConfig("SHARDING_GO"),
				ShardingGateway::SEARCH_KEY => null,
			]);
		}

		return static::$_instance;
	}
}
