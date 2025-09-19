<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Icap\IcapConfig;
use BaseFrame\Server\ServerProvider;

/**
 * класс конфига
 */
class Domain_Config_Entity_Icap extends IcapConfig
{
	/** @var Domain_Config_Entity_Icap|null для синглтона */
	protected static Domain_Config_Entity_Icap | null $_instance = null;

	/**
	 * Создать инстанс
	 */
	public static function instance(int $user_id): static
	{

		if (!is_null(static::$_instance)) {
			return static::$_instance;
		}

		$config = getConfig("ICAP");

		if (ServerProvider::isTest()) {
			$config = ShardingGateway::cache()->get(self::_getMcacheMockKey(DOMINO_ID . "_$user_id")) ?: $config;
		}

		static::$_instance = new static($config);

		return static::$_instance;
	}

	/**
	 * Установить мок
	 */
	public static function setMock(int $user_id, array $config): void
	{

		if (!ServerProvider::isTest()) {
			throw new ParseFatalException("only for test server");
		}
		ShardingGateway::cache()->set(self::_getMcacheMockKey(DOMINO_ID . "_$user_id"), $config);

		static::$_instance = null;
	}

	/**
	 * Очистить мок
	 */
	public static function clearMock(int $user_id): void
	{

		if (!ServerProvider::isTest()) {
			throw new ParseFatalException("only for test server");
		}
		ShardingGateway::cache()->delete(self::_getMcacheMockKey(DOMINO_ID . "_$user_id"));

		static::$_instance = null;
	}
}
