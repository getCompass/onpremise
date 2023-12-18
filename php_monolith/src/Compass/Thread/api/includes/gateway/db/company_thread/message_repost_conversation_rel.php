<?php

namespace Compass\Thread;

/**
 * класс-интерфейс для таблицы cloud_thread_{Year}.message_repost_conversation_rel
 */
class Gateway_Db_CompanyThread_MessageRepostConversationRel extends Gateway_Db_CompanyThread_Main {

	protected const _TABLE_KEY = "message_repost_conversation_rel";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод для добавления записи
	 *
	 * @param array $insert
	 *
	 * @return void
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function insert(array $insert):void {

		ShardingGateway::database(self::_getDbKey())->insert(self::_getTable(), $insert);
	}

	/**
	 * Метод для добавления массива записей
	 *
	 * @param array $insert
	 *
	 * @return void
	 * @throws \parseException
	 */
	public static function insertArray(array $insert):void {

		ShardingGateway::database(self::_getDbKey())->insertArray(self::_getTable(), $insert);
	}

	/**
	 * Метод для обновления записи
	 *
	 * @param string $thread_map
	 * @param string $message_map
	 * @param array  $set
	 *
	 * @return void
	 * @throws \parseException
	 */
	public static function set(string $thread_map, string $message_map, array $set):void {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE `thread_map` = ?s AND `message_map` = ?s LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_getTable(), $set, $thread_map, $message_map, 1);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	/**
	 * Получаем таблицу
	 * @return string
	 */
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}