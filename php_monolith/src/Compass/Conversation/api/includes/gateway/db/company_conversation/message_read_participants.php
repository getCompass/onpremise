<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Gateway\QueryFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс-интерфейс для таблицы dynamic в company_conversation
 */
class Gateway_Db_CompanyConversation_MessageReadParticipants extends Gateway_Db_CompanyConversation_Main
{
	protected const _TABLE_KEY = "message_read_participants";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод для получения записи
	public static function getOne(string $conversation_map, int $conversation_message_index, int $user_id): Struct_Db_CompanyConversation_MessageReadParticipant
	{

		$shard_key = static::_getDbKey();
		$table_key = static::_getTable($conversation_map);

		// запрос проверен на EXPLAIN (INDEX=PRIMARY) Федореев М. 06.05.2025
		$query = "SELECT * FROM `?p` WHERE conversation_map = ?s AND conversation_message_index = ?i AND user_id = ?i LIMIT ?i";
		$row   = static::_connect($shard_key)->getOne($query, $table_key, $conversation_map, $conversation_message_index, $user_id, 1);

		if (!isset($dynamic_row["conversation_map"])) {
			throw new RowNotFoundException("cant find message read participant");
		}

		return self::_rowToObject($row);
	}

	/**
	 * Получить прочитавших сообщение пользователей
	 *
	 * @return Struct_Db_CompanyConversation_MessageReadParticipant_Participant[]
	 * @throws QueryFatalException
	 */
	public static function getReadParticipants(string $conversation_map, int $conversation_message_index): array
	{

		$shard_key = static::_getDbKey();
		$table_key = static::_getTable($conversation_map);

		// запрос проверен на EXPLAIN (INDEX=PRIMARY) Федореев М. 07.03.2025
		$query = "SELECT `user_id`, `conversation_message_index`, `read_at` FROM `?p` WHERE `conversation_map` = ?s AND `conversation_message_index` >= ?i 
                                                ORDER BY `conversation_message_index` ASC LIMIT ?i";
		$result = static::_connect($shard_key)->getAll($query, $table_key, $conversation_map, $conversation_message_index, 10000);

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
	protected static function _getTable(string $conversation_map): string
	{

		$table_id = \CompassApp\Pack\Conversation::getTableId($conversation_map);

		return self::_TABLE_KEY . "_" . $table_id;
	}

	/**
	 * Создаем структуру из строки бд
	 */
	protected static function _rowToObject(array $row): Struct_Db_CompanyConversation_MessageReadParticipant
	{

		return new Struct_Db_CompanyConversation_MessageReadParticipant(
			$row["conversation_map"],
			$row["conversation_message_index"],
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
	protected static function _rowToParticipantObject(array $row): Struct_Db_CompanyConversation_MessageReadParticipant_Participant
	{

		return new Struct_Db_CompanyConversation_MessageReadParticipant_Participant(
			$row["user_id"],
			$row["read_at"],
		);
	}
}
