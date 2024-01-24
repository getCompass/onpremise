<?php

namespace Compass\Conversation;

/**
 * класс-интерфейс для таблицы company_conversation.user_inbox
 */
class Gateway_Db_CompanyConversation_UserInbox extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "user_inbox";

	// создание записи
	public static function insert(array $insert):void {

		static::_connect(self::_getDbKey())->insert(self::_getTable(), $insert);
	}

	// получение записи
	public static function getOne(int $user_id):array {

		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		return static::_connect(self::_getDbKey())->getOne($query, self::_getTable(), $user_id, 1);
	}

	// обновление записи
	public static function set(int $user_id, array $set):void {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i LIMIT ?i";
		static::_connect(self::_getDbKey())->update($query, self::_getTable(), $set, $user_id, 1);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}