<?php

namespace Compass\Conversation;

/**
 * Класс-интерфейс для базы данных space_search.
 */
class Gateway_Db_SpaceSearch_Main extends Gateway_Db_Db {

	/** @var string название базы данных */
	protected const _DB_KEY = "space_search";

	// получаем шард ключ для базы данных
	protected static function _getDbKey():string {

		return self::_DB_KEY;
	}
}