<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Класс для работы с типом сервера для отдачи клиентам
 */
class Domain_System_Entity_ServerType {

	public const SERVER_TYPE_SAAS         = "saas";
	public const SERVER_TYPE_ON_PREMISE   = "on-premise";
	public const SERVER_TYPE_YANDEX_CLOUD = "yandex_cloud";

	/**
	 * Выполняет отправку сообщения.
	 */
	public static function getServerType():string {

		if (ServerProvider::isSaas()) {
			return self::SERVER_TYPE_SAAS;
		}

		if (ServerProvider::isYandexCloudMarketplace()) {
			return self::SERVER_TYPE_YANDEX_CLOUD;
		}

		return self::SERVER_TYPE_ON_PREMISE;
	}
}