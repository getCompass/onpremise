<?php

namespace Compass\Thread;

use BaseFrame\Exception\Gateway\QueryFatalException;
use CompassApp\Pack\Message;

/**
 * класс-интерфейс для таблицы dynamic в company_thread
 */
class Gateway_Db_CompanyThread_MessageReadParticipants extends Gateway_Db_CompanyThread_Main
{
	protected const _TABLE_KEY = "message_read_participants";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод для обновления записи
	public static function set(string $thread_map, int $user_id, array $set): void
	{

		$shard_key = static::_getDbKey();
		$table_key = static::_getTable($thread_map);

		// запрос проверен на EXPLAIN (INDEX=PRIMARY) Федореев М. 07.03.2025
		$query = "UPDATE `?p` SET ?u WHERE thread_map = ?s AND user_id = ?i LIMIT ?i";
		static::_connect($shard_key)->update($query, $table_key, $set, $thread_map, $user_id, 1);
	}

	// метод для получения записи
	public static function getOne(string $thread_map, int $user_id): Struct_Db_CompanyThread_MessageReadParticipant
	{

		$shard_key = static::_getDbKey();
		$table_key = static::_getTable($thread_map);

		// запрос проверен на EXPLAIN (INDEX=PRIMARY) Федореев М. 07.03.2025
		$query = "SELECT * FROM `?p` WHERE thread_map = ?s AND user_id = ?i LIMIT ?i";
		$row   = static::_connect($shard_key)->getOne($query, $table_key, $thread_map, $user_id, 1);

		if (!isset($dynamic_row["thread_map"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToObject($row);
	}

	/**
	 * Получить прочитавших сообщение пользователей
	 *
	 * @return Struct_Db_CompanyThread_MessageReadParticipant_Participant[]
	 * @throws QueryFatalException
	 */
	public static function getReadParticipants(string $thread_map, int $thread_message_index): array
	{

		$shard_key = static::_getDbKey();
		$table_key = static::_getTable($thread_map);

		// запрос проверен на EXPLAIN (INDEX=PRIMARY) Федореев М. 07.03.2025
		$query = "SELECT `user_id`, `thread_message_index`, `read_at` FROM `?p` WHERE `thread_map` = ?s AND `thread_message_index` >= ?i 
                                                ORDER BY `thread_message_index` ASC LIMIT ?i";
		$result = static::_connect($shard_key)->getAll($query, $table_key, $thread_map, $thread_message_index, 10000);

		$output = [];
		foreach ($result as $row) {

			if (isset($output[$row["user_id"]])) {
				continue;
			}

			$output[$row["user_id"]] = self::_rowToParticipantObject($row);
		}

		return $output;
	}

	/**
	 * Получаем запись для определенного сообщения
	 *
	 * @throws QueryFatalException
	 */
	public static function getByMessageMap(string $message_map): array
	{

		$shard_key = static::_getDbKey();
		$table_key = static::_getTable(Message\Thread::getThreadMap($message_map));

		// запрос проверен на EXPLAIN (INDEX=message_map) Федореев М. 07.03.2025
		$query  = "SELECT * FROM `?p` WHERE message_map = ?s LIMIT ?i";
		$result = static::_connect($shard_key)->getAll($query, $table_key, $message_map, 10000);

		return array_map(static fn (array $el) => self::_rowToObject($el), $result);
	}

	/**
	 * Получаем запись для определенных сообщений
	 *
	 * @return Struct_Db_CompanyThread_MessageReadParticipant[]
	 * @throws QueryFatalException
	 */
	public static function getByMessageMapList(array $message_map_list): array
	{

		$shard_key = static::_getDbKey();

		$grouped_by_table_list = [];
		foreach ($message_map_list as $message_map) {

			$table_key                           = static::_getTable(Message\Thread::getThreadMap($message_map));
			$grouped_by_table_list[$table_key][] = $message_map;
		}

		// запрос проверен на EXPLAIN (INDEX=message_map) Федореев М. 07.03.2025
		$query = "SELECT * FROM `?p` WHERE message_map IN (?a) LIMIT ?i";

		// делаем запросы
		$grouped_query_list = [];
		foreach ($grouped_by_table_list as $table_key => $message_map_list) {
			$grouped_query_list[] = static::_connect($shard_key)->getAll($query, $table_key, $message_map_list, count($message_map_list));
		}

		// собираем массив объектов
		$output_list = [];
		foreach ($grouped_query_list as $query_list) {

			foreach ($query_list as $row) {

				$object        = self::_rowToObject($row);
				$output_list[] = $object;
			}
		}

		return $output_list;
	}

	/**
	 * Удалить записи из таблицы по message_created_at
	 *
	 * @throws QueryFatalException
	 */
	public static function deleteByMessageCreatedAt(int $table_shard, int $message_created_at): void
	{

		$shard_key           = static::_getDbKey();
		$table_key           = self::_TABLE_KEY . "_" . $table_shard;
		$limit               = 1000;
		$total_deleted_count = 0;

		// удаляем в цикле по 1000 записей
		do {

			// запрос проверен на EXPLAIN (INDEX=message_created_at) Федореев М. 11.03.2025
			$query         = "DELETE FROM `?p` WHERE `message_created_at` < ?i LIMIT ?i";
			$deleted_count = static::_connect($shard_key)->delete($query, $table_key, $message_created_at, $limit);
			$total_deleted_count += $deleted_count;
		} while ($deleted_count == $limit);

		if ($total_deleted_count > 0) {

			// оптимизируем таблицу
			self::_optimize($table_shard);
		}
	}

	/**
	 * Оптимизировать таблицу
	 */
	protected static function _optimize(int $table_shard): void
	{

		$shard_key = static::_getDbKey();
		$table_key = self::_TABLE_KEY . "_" . $table_shard;

		$query = "OPTIMIZE TABLE `{$shard_key}`.`{$table_key}`;";
		static::_connect($shard_key)->execQuery($query);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTable(string $thread_map): string
	{

		$table_id = \CompassApp\Pack\Thread::getTableId($thread_map);

		return self::_TABLE_KEY . "_" . $table_id;
	}

	/**
	 * Создаем структуру из строки бд
	 */
	protected static function _rowToObject(array $row): Struct_Db_CompanyThread_MessageReadParticipant
	{

		return new Struct_Db_CompanyThread_MessageReadParticipant(
			$row["thread_map"],
			$row["thread_message_index"],
			$row["user_id"],
			$row["read_at"],
			$row["message_created_at"],
			$row["created_at"],
			$row["updated_at"],
			$row["message_map"],
		);
	}

	/**
	 * Создаем структуру прочитанного сообщения из строки бд
	 */
	protected static function _rowToParticipantObject(array $row): Struct_Db_CompanyThread_MessageReadParticipant_Participant
	{

		return new Struct_Db_CompanyThread_MessageReadParticipant_Participant(
			$row["user_id"],
			$row["read_at"],
		);
	}
}
