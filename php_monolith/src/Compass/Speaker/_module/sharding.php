<?php

declare(strict_types=1);
/** @noinspection DuplicatedCode */

namespace Compass\Speaker;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс шардинга для изоляции настроек подключения внутри модуля.
 * @package Compass\Speaker
 */
class ShardingGateway extends \ShardingGateway
{
	protected static ?ShardingGateway $_instance = null;

	/**
	 * Инициализирует экземпляр работы с шардящимися подключениями.
	 *
	 * @throws \parseException
	 */
	public static function instance(): static
	{

		if (is_null(static::$_instance)) {

			static::$_instance = new ShardingGateway([
				ShardingGateway::DB_KEY         => getConfig("SHARDING_MYSQL"),
				ShardingGateway::BUS_KEY        => getConfig("SHARDING_RABBIT"),
				ShardingGateway::CACHE_KEY      => getConfig("SHARDING_MCACHE"),
				ShardingGateway::RPC_KEY        => getConfig("SHARDING_GO"),
				ShardingGateway::SEARCH_KEY     => getConfig("SHARDING_MANTICORE"),
				ShardingGateway::MSG_BROKER_KEY => null,
			]);
		}

		return static::$_instance;
	}

	/**
	 * Возвращает класс для работы с шиной данных.
	 */
	public static function cache(): \mCache
	{

		// получаем конфиг с базой данных
		return \CompassApp\Gateway\Memcached::configured(static::instance()->_config_list[static::CACHE_KEY]);
	}

	/**
	 * Возвращает класс для работы с шиной данных.
	 *
	 * @throws ParseFatalException
	 */
	public static function rabbit(string $bus = "bus"): \Rabbit
	{

		$rabbit = parent::rabbit();
		$rabbit->setPostfixQueue((string) (COMPANY_ID % 10));
		return $rabbit;
	}
}
