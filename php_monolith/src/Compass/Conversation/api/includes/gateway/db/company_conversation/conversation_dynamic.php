<?php

namespace Compass\Conversation;

/**
 * класс-интерфейс для таблицы dynamic в company_conversation
 */
class Gateway_Db_CompanyConversation_ConversationDynamic extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "conversation_dynamic";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод для обновления записи
	public static function set(string $conversation_map, array $set):void {

		$shard_key = static::_getDbKey();

		$query = "UPDATE `?p` SET ?u WHERE conversation_map = ?s LIMIT ?i";
		static::_connect($shard_key)->update($query, self::_getTable(), $set, $conversation_map, 1);
	}

	// метод для получения записи
	public static function getOne(string $conversation_map):Struct_Db_CompanyConversation_ConversationDynamic {

		$shard_key = static::_getDbKey();

		$query       = "SELECT * FROM `?p` WHERE conversation_map = ?s LIMIT ?i";
		$dynamic_row = static::_connect($shard_key)->getOne($query, self::_getTable(), $conversation_map, 1);

		if (!isset($dynamic_row["conversation_map"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToObject($dynamic_row);
	}

	/**
	 * получаем все записи
	 *
	 * @param array $conversation_map_list
	 * @param bool  $assoc_list
	 *
	 * @return Struct_Db_CompanyConversation_ConversationDynamic[]
	 */
	public static function getAll(array $conversation_map_list, bool $assoc_list = false):array {

		$query        = "SELECT * FROM `?p` WHERE conversation_map IN (?a) LIMIT ?i";
		$dynamic_list = static::_connect(self::_getDbKey())->getAll($query, self::_getTable(), $conversation_map_list, count($conversation_map_list));

		$output = [];
		foreach ($dynamic_list as $item) {

			$object = self::_rowToObject($item);

			// если нужен ассоциативный массив на выходе
			if ($assoc_list) {

				$output[$object->conversation_map] = $object;
				continue;
			}
			$output[] = $object;
		}
		return $output;
	}

	// метод для получения записи на обновление
	public static function getForUpdate(string $conversation_map):Struct_Db_CompanyConversation_ConversationDynamic {

		$shard_key = static::_getDbKey();

		$query       = "SELECT * FROM `?p` WHERE conversation_map = ?s LIMIT ?i FOR UPDATE";
		$dynamic_row = static::_connect($shard_key)->getOne($query, self::_getTable(), $conversation_map, 1);

		if (!isset($dynamic_row["conversation_map"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToObject($dynamic_row);
	}

	/**
	 * Возвращает все записи по порядку.
	 */
	public static function getOrdered(int $limit, int $offset):array {

		$query  = "SELECT * FROM `?p` WHERE TRUE LIMIT ?i OFFSET ?i";
		$result = static::_connect(static::_getDbKey())->getAll($query, self::_getTable(), $limit, $offset);

		return array_map(static fn(array $el) => self::_rowToObject($el), $result);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}

	/**
	 * Создаем сткруктуру из строки бд
	 * @long - большая структура
	 */
	protected static function _rowToObject(array $row):Struct_Db_CompanyConversation_ConversationDynamic {

		$row["conversation_clear_info"] = fromJson($row["conversation_clear_info"]);
		$row["user_clear_info"]         = fromJson($row["user_clear_info"]);
		$row["user_mute_info"]          = fromJson($row["user_mute_info"]);
		$row["user_file_clear_info"]    = fromJson($row["user_file_clear_info"]);

		return new Struct_Db_CompanyConversation_ConversationDynamic(
			$row["conversation_map"],
			$row["is_locked"],
			$row["last_block_id"],
			$row["start_block_id"],
			$row["total_message_count"],
			$row["total_action_count"],
			$row["file_count"],
			$row["image_count"],
			$row["video_count"],
			$row["created_at"],
			$row["updated_at"],
			$row["messages_updated_at"],
			$row["reactions_updated_at"],
			$row["threads_updated_at"],
			$row["messages_updated_version"],
			$row["reactions_updated_version"],
			$row["threads_updated_version"],
			$row["user_mute_info"],
			$row["user_clear_info"],
			$row["user_file_clear_info"],
			$row["conversation_clear_info"],
		);
	}
}