<?php

namespace Compass\Conversation;

/**
 * Базовый класс для уровневых блокировок
 */
abstract class Type_Antispam_Main {

	/**
	 * Есть ли необходимость проверять блокировку
	 *
	 */
	public static function needCheckIsBlocked():bool {

		if (isBackendTest() && !isNeedAntispam()) {
			return true;
		}

		return false;
	}

	/**
	 * Создает подключение к базе данных
	 */
	protected static function _connect():\myPDObasic {

		return ShardingGateway::database(static::_DB_KEY);
	}
}