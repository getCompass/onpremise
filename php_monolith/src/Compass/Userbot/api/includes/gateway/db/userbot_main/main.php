<?php

namespace Compass\Userbot;

/**
 * Базоый класс для работы с базой userbot_main
 */
class Gateway_Db_UserbotMain_Main extends Gateway_Db_Db {

	protected const _DB_KEY = "userbot_main";

	/**
	 * Получить имя БД
	 *
	 */
	protected static function _getDbKey():string {

		return static::_DB_KEY;
	}
}
