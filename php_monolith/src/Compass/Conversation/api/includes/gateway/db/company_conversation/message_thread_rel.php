<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы message_thread_rel в company_conversation
 */
class Gateway_Db_CompanyConversation_MessageThreadRel extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "message_thread_rel";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Позволяет добавить запись в базу
	 *
	 * @throws cs_Message_AlreadyContainsThread
	 * @throws \queryException
	 */
	public static function insert(string $conversation_map, array $insert):void {

		try {
			static::_connect(self::_getDbKey())->insert(self::_getTable(), $insert, false);
		} catch (\PDOException $exception) {

			// если это дубликат
			if ($exception->getCode() == 23000) {
				throw new cs_Message_AlreadyContainsThread();
			}

			throw $exception;
		}
	}

	/**
	 * Позволяет обновить запись в базе
	 *
	 */
	public static function set(string $conversation_map, string $message_map, array $set):void {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY KEY`)
		$query = "UPDATE `?p` SET ?u WHERE conversation_map = ?s AND `message_map` = ?s LIMIT ?i";
		static::_connect(self::_getDbKey())->update($query, self::_getTable(), $set, $conversation_map, $message_map, 1);
	}

	/**
	 * Позволяет получить записи для конкретного блока
	 *
	 */
	public static function getThreadListByBlock(string $conversation_map, int $block_id):array {

		$count = self::getCount($conversation_map, [$block_id]);

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY KEY`)
		$query = "SELECT * FROM `?p` WHERE conversation_map = ?s AND `block_id` = ?i LIMIT ?i";
		return static::_connect(self::_getDbKey())
			->getAll($query, self::_getTable(), $conversation_map, $block_id, $count);
	}

	/**
	 * Позволяет получить записи по переданному списку блоков
	 *
	 * @return Struct_Db_CompanyConversation_MessageThreadRel[]
	 * @throws \parseException
	 */
	public static function getThreadListByBlockList(string $conversation_map, array $block_id_list):array {

		$result = [];
		$count  = self::getCount($conversation_map, $block_id_list);

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY KEY`)
		$query    = "SELECT * FROM `?p` WHERE conversation_map = ?s AND `block_id` IN (?a) LIMIT ?i";
		$row_list = static::_connect(self::_getDbKey())
			->getAll($query, self::_getTable(), $conversation_map, $block_id_list, $count);

		foreach ($row_list as $row) {
			$result[] = self::_rowToObject($row);
		}

		return $result;
	}

	/**
	 * Позволяет получить запись из базы для конкретного сообщения
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function getOneByMessageMap(string $conversation_map, string $message_map):Struct_Db_CompanyConversation_MessageThreadRel {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY KEY`)
		$query = "SELECT * FROM `?p` WHERE conversation_map = ?s AND `message_map` = ?s LIMIT ?i";
		$row   = static::_connect(self::_getDbKey())
			->getOne($query, self::_getTable(), $conversation_map, $message_map, 1);

		if (!isset($row["message_map"])) {
			throw new \cs_RowIsEmpty("thread rel not found");
		}

		return self::_rowToObject($row);
	}

	/**
	 * Позволяет получить запись из базы для конкретного треда
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function getOneByThreadMap(string $conversation_map, string $thread_map):Struct_Db_CompanyConversation_MessageThreadRel {

		// запрос проверен на EXPLAIN (INDEX=`conversation_thread`)
		$query = "SELECT * FROM `?p` WHERE conversation_map = ?s AND `thread_map` = ?s LIMIT ?i";

		$row = static::_connect(self::_getDbKey())
			->getOne($query, self::_getTable(), $conversation_map, $thread_map, 1);

		if (!isset($row["thread_map"])) {
			throw new \cs_RowIsEmpty("thread rel not found");
		}

		return self::_rowToObject($row);
	}

	/**
	 * Позволяет получить записи из базы для списка сообщений
	 *
	 */
	public static function getThreadListByMessageMapList(string $conversation_map, array $message_map_list):array {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY KEY`)
		$query = "SELECT * FROM `?p` WHERE conversation_map = ?s AND `message_map` IN (?a) LIMIT ?i";
		return static::_connect(self::_getDbKey())
			->getAll($query, self::_getTable(), $conversation_map, $message_map_list, count($message_map_list));
	}

	/**
	 * Позволяет узнать кол-во записей, для LIMIT по block_id
	 *
	 */
	public static function getCount(string $conversation_map, array $block_id_list):int {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY KEY`)
		$query = "SELECT COUNT(*) AS count FROM `?p` WHERE conversation_map = ?s AND `block_id` IN (?a) LIMIT ?i";
		$row   = static::_connect(self::_getDbKey())->getOne($query, self::_getTable(), $conversation_map, $block_id_list, 1);

		return $row["count"] ?? 0;
	}

	/**
	 * Возвращает конкретные блоки для конкретных диалогов.
	 *
	 * @param array $block_list_by_conversation_map
	 *
	 * @return Struct_Db_CompanyConversation_MessageThreadRel[]
	 * @throws \parseException
	 */
	public static function getSpecifiedList(array $block_list_by_conversation_map):array {

		$table_data = [];

		foreach ($block_list_by_conversation_map as $conversation_map => $block_id_list) {
			array_push($table_data, $conversation_map, $block_id_list);
		}

		$limit = self::getSpecifiedCount($block_list_by_conversation_map);

		$output       = [];
		$sharding_key = self::_getDbKey();
		$table_name   = self::_getTable();

		// EXPLAIN USE INDEX (`conversation_map_and_block_id`)
		$where_expression = implode(" OR ", array_fill(0, count($table_data) / 2, "(`conversation_map` = ?s AND `block_id` IN (?a))"));
		$query            = "SELECT * FROM `?p` USE INDEX(`conversation_map_and_block_id`) WHERE $where_expression LIMIT ?i";

		$block_list = static::_connect($sharding_key)->getAll(...[$query, $table_name, ...$table_data, $limit]);
		$output[]   = array_map(static fn(array $row):Struct_Db_CompanyConversation_MessageThreadRel => static::_rowToObject($row), $block_list);

		return array_merge(...$output);
	}

	/**
	 * Позволяет узнать кол-во записей, для списка диалогов
	 */
	public static function getSpecifiedCount(array $block_list_by_conversation_map):int {

		$table_data = [];
		$limit      = 0;

		foreach ($block_list_by_conversation_map as $conversation_map => $block_id_list) {

			$limit += count($block_id_list);
			array_push($table_data, $conversation_map, $block_id_list);
		}

		$sharding_key = self::_getDbKey();
		$table_name   = self::_getTable();

		// EXPLAIN USE INDEX (`conversation_map_and_block_id`)
		$where_expression = implode(" OR ", array_fill(0, count($table_data) / 2, "(`conversation_map` = ?s AND `block_id` IN (?a))"));
		$query            = "SELECT COUNT(*) AS count FROM `?p` USE INDEX(`conversation_map_and_block_id`) WHERE $where_expression LIMIT ?i";
		$row              = static::_connect($sharding_key)->getOne(...[$query, $table_name, ...$table_data, $limit]);

		return $row["count"] ?? 0;
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	/**
	 * Возвращает таблицу для sharding
	 *
	 */
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}

	/**
	 * Приводим запись в объект
	 *
	 * @throws \parseException
	 */
	protected static function _rowToObject(array $row):Struct_Db_CompanyConversation_MessageThreadRel {

		// приводим запись бд к формату
		$row = self::_formatJson($row);

		foreach ($row as $field => $_) {
			if (!property_exists(Struct_Db_CompanyConversation_MessageThreadRel::class, $field)) {

				throw new ParseFatalException("send unknown field '{$field}'");
			}
		}

		return new Struct_Db_CompanyConversation_MessageThreadRel(
			$row["conversation_map"],
			$row["message_map"],
			$row["thread_map"],
			$row["block_id"],
			$row["extra"],
		);
	}

	/**
	 * Распарсить json поля
	 *
	 */
	protected static function _formatJson(array $row):array {

		if (isset($row["thread_map"])) {
			$row["extra"] = fromJson($row["extra"]);
		}

		return $row;
	}
}