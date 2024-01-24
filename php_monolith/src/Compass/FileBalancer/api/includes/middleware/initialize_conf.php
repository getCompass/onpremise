<?php

namespace Compass\FileBalancer;

use BaseFrame\Router\Request;

/**
 * Class Middleware_InitializeConf
 */
class Middleware_InitializeConf implements \BaseFrame\Router\Middleware\Main {

	/**
	 * Функция инициализации конфигов
	 */
	public static function handle(Request $request):Request {

		\BaseFrame\Conf\ConfHandler::init(
			getConfig("SHARDING_GO"), getConfig("SHARDING_SPHINX"), getConfig("SHARDING_RABBIT"),
			getConfig("SHARDING_MYSQL"), getConfig("SHARDING_MCACHE"), getConfig("GLOBAL_OFFICE_IP")
		);

		return $request;
	}
}