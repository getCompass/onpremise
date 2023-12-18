<?php

namespace Compass\Conversation;

/**
 * класс-интерфейс для таблицы invite_list
 */
class Gateway_Db_CompanyConversation_InviteList extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "invite_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод создает запись
	public static function insert(int $meta_id, int $invite_type, int $created_at, int $year):int {

		$insert = [
			"meta_id"    => $meta_id,
            "year"       => $year,
			"type"       => $invite_type,
			"created_at" => $created_at,
			"updated_at" => 0,
		];

		return static::_connect(static::_getDbKey())->insert(self::_getTable(), $insert, false);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}