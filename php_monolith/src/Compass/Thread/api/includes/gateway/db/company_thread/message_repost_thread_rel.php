<?php

namespace Compass\Thread;

/**
 * класс-интерфейс для таблицы cloud_thread_{Year}.message_repost_thread_rel
 */
class Gateway_Db_CompanyThread_MessageRepostThreadRel extends Gateway_Db_CompanyThread_Main {

	protected const _TABLE_KEY = "message_repost_thread_rel";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод для добавления массива записей
	public static function insertArray(array $insert):void {

		ShardingGateway::database(self::_getDbKey())->insertArray(self::_getTable(), $insert);
	}

	// метод для обновления записи
	public static function set(string $thread_map, string $message_map, array $set):void {

		$query = "UPDATE `?p` SET ?u WHERE `thread_map` = ?s AND `message_map` = ?s LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_getTable(), $set, $thread_map, $message_map, 1);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}