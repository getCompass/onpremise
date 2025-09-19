<?php

namespace Compass\Conversation;

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


		if (!is_null(static::$_instance) && !ServerProvider::isTest()) {
			return static::$_instance;
		}

		$config = getConfig("ICAP");

		if (ServerProvider::isTest()) {
			$config = ShardingGateway::cache()->get(self::_getMcacheMockKey(DOMINO_ID . "_$user_id")) ?: $config;
		}

		static::$_instance = new static($config);

		return static::$_instance;
	}
}
