<?php

namespace Compass\Conversation;

/**
 * класс-интерфейс для таблицы message_repost_thread_rel в company_conversation
 */
class Gateway_Db_CompanyConversation_MessageRepostThreadRel extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "message_repost_thread_rel";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод для добавления массива записей
	public static function insertArray(array $insert):void {

		static::_connect(self::_getDbKey())->insertArray(self::_getTable(), $insert);
	}

	// метод для обновления записи
	public static function set(string $conversation_map, string $message_map, array $set):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE conversation_map = ?s AND `message_map` = ?s LIMIT ?i";
		static::_connect(self::_getDbKey())->update($query, self::_getTable(), $set, $conversation_map, $message_map, 1);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}