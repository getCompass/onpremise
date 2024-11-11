<?php

namespace Compass\Conversation;

/**
 * класс-интерфейс для таблицы conversation_file в company_conversation
 */
class Gateway_Db_CompanyConversation_ConversationFile extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "conversation_file";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод для создания записи
	public static function insert(Struct_Db_CompanyConversation_ConversationFile $insert):void {

		$sharding_key = self::_getDbKey();
		static::_connect($sharding_key)->insert(self::_getTable(), (array) $insert);
	}

	// метод помечает список файлов удалненными по родительскому
	public static function setDeletedListByParentMapList(array $file_uuid_list):void {

		$set = [
			"is_deleted" => 1,
			"updated_at" => time(),
		];

		$sharding_key = self::_getDbKey();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `file_uuid` IN (?a) LIMIT ?i";
		static::_connect($sharding_key)->update($query, self::_getTable(), $set, $file_uuid_list, count($file_uuid_list));
	}

	// метод помечает файлы удаленными по message_map
	public static function setDeletedListByConversationMessageMapList(string $conversation_map, array $message_map_list, int $count):void {

		$set = [
			"is_deleted" => 1,
			"updated_at" => time(),
		];

		// only script индекс не нужен
		$sharding_key = self::_getDbKey();
		$query        = "UPDATE `?p` SET ?u WHERE `conversation_map` = ?s AND `conversation_message_map` IN (?a) LIMIT ?i";
		static::_connect($sharding_key)->update($query, self::_getTable(), $set, $conversation_map, $message_map_list, $count);
	}

	/**
	 * метод для получения списка файлов
	 *
	 * @return Struct_Db_CompanyConversation_ConversationFile[]
	 */
	public static function getSortedList(string $conversation_map, array $file_type_in, array $parent_type_list, int $user_clear_until_at, int $count, int $below_id):array {

		$sharding_key = self::_getDbKey();

		// сначала получаем ключи
		// запрос проверен на EXPLAIN (INDEX=`grom_user_files`)
		$query    = "SELECT `file_uuid` FROM `?p` FORCE INDEX (`grom_user_files`) WHERE `conversation_map` = ?s AND `is_deleted` = ?i AND `file_type` IN (?a) 
                                                    AND `parent_type` IN (?a) AND `conversation_message_created_at` > ?i AND `row_id` <= ?i ORDER BY `row_id` DESC LIMIT ?i";
		$row_list = static::_connect($sharding_key)->getAll($query, self::_getTable(),
			$conversation_map, 0, $file_type_in, $parent_type_list, $user_clear_until_at, $below_id, $count);
		if (count($row_list) < 1) {
			return [];
		}

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$file_uuid_list = formatIn($row_list, "file_uuid");
		$query          = "SELECT * FROM `?p` WHERE `file_uuid` IN (?a) LIMIT ?i";
		$file_list      = static::_connect($sharding_key)->getAll($query, self::_getTable(), $file_uuid_list, count($file_uuid_list));

		// сортируем по row_id в порядке убывания
		usort($file_list, function(array $a, array $b) {

			return $b["row_id"] <=> $a["row_id"];
		});

		// форматируем строки в массив
		foreach ($file_list as $k => $v) {
			$file_list[$k] = self::_formatRow($v);
		}

		return $file_list;
	}

	/**
	 * метод для получения списка файлов для определенного пользователя
	 *
	 * @return Struct_Db_CompanyConversation_ConversationFile[]
	 */
	public static function getSortedListByUserId(string $conversation_map, array $file_type_in, array $parent_type_list,
								   int    $user_clear_until_at, int $count, int $below_id, int $user_id):array {

		$sharding_key = self::_getDbKey();

		// сначала получаем ключи
		// запрос проверен на EXPLAIN (INDEX=`grom_user_files`)
		$query    = "SELECT `file_uuid` FROM `?p` FORCE INDEX (`grom_user_files`) WHERE `conversation_map` = ?s AND `is_deleted` = ?i AND `file_type` IN (?a) 
                                                   AND `parent_type` IN (?a) AND `conversation_message_created_at` > ?i AND `row_id` <= ?i AND `user_id` = ?i 
                                            ORDER BY `row_id` DESC LIMIT ?i";
		$row_list = static::_connect($sharding_key)->getAll($query, self::_getTable(),
			$conversation_map, 0, $file_type_in, $parent_type_list, $user_clear_until_at, $below_id, $user_id, $count);
		if (count($row_list) < 1) {
			return [];
		}

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$file_uuid_list = formatIn($row_list, "file_uuid");
		$query          = "SELECT * FROM `?p` WHERE `file_uuid` IN (?a) LIMIT ?i";
		$file_list      = static::_connect($sharding_key)->getAll($query, self::_getTable(), $file_uuid_list, count($file_uuid_list));

		// сортируем по row_id в порядке убывания
		usort($file_list, function(array $a, array $b) {

			return $b["row_id"] <=> $a["row_id"];
		});

		// форматируем строки в массив
		foreach ($file_list as $k => $v) {
			$file_list[$k] = self::_formatRow($v);
		}

		return $file_list;
	}

	/**
	 * метод для получения списка файлов
	 *
	 * @return Struct_Db_CompanyConversation_ConversationFile[]
	 */
	public static function getList(string $conversation_map, array $file_type_in, array $parent_type_list, int $user_clear_until_at, int $count, int $offset):array {

		$sharding_key = self::_getDbKey();

		// запрос проверен на EXPLAIN (INDEX=`grom_viewer_user_files`)
		$query    = "SELECT `file_uuid` FROM `?p` FORCE INDEX (`grom_viewer_user_files`) WHERE `conversation_map` = ?s AND `is_deleted` = ?i AND `file_type` IN (?a) 
                                                    AND `parent_type` IN (?a) AND `conversation_message_created_at` > ?i ORDER BY `created_at` DESC LIMIT ?i OFFSET ?i";
		$row_list = static::_connect($sharding_key)->getAll($query, self::_getTable(), $conversation_map, 0, $file_type_in, $parent_type_list,
			$user_clear_until_at, $count, $offset);
		if (count($row_list) < 1) {
			return [];
		}

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$file_uuid_list = formatIn($row_list, "file_uuid");
		$query          = "SELECT * FROM `?p` WHERE `file_uuid` IN (?a) LIMIT ?i";
		$file_list      = static::_connect($sharding_key)->getAll($query, self::_getTable(), $file_uuid_list, count($file_uuid_list));

		// сортируем по created_at в порядке убывания
		usort($file_list, function(array $a, array $b) {

			return $b["created_at"] <=> $a["created_at"];
		});

		// форматируем строки в массив
		foreach ($file_list as $k => $v) {
			$file_list[$k] = self::_formatRow($v);
		}

		return $file_list;
	}

	/**
	 * метод для получения списка файлов прикрепленных к диалогу пользователем
	 *
	 * @return Struct_Db_CompanyConversation_ConversationFile[]
	 */
	public static function getUserFiles(string $conversation_map, int $user_id, array $file_type_in, array $parent_type_list,
							int    $user_clear_until_at, int $count, int $offset):array {

		$sharding_key = self::_getDbKey();

		// запрос проверен на EXPLAIN (INDEX=`grom_viewer_user_files`)
		$query    = "SELECT `file_uuid` FROM `?p` FORCE INDEX (`grom_viewer_user_files`) WHERE `conversation_map` = ?s AND `is_deleted` = ?i AND `file_type` IN (?a) 
                                                  AND `parent_type` IN (?a) AND `conversation_message_created_at` > ?i AND `user_id` = ?i 
                                                  ORDER BY `created_at` DESC LIMIT ?i OFFSET ?i";
		$row_list = static::_connect($sharding_key)->getAll($query, self::_getTable(), $conversation_map, 0, $file_type_in, $parent_type_list,
			$user_clear_until_at, $user_id, $count, $offset);
		if (count($row_list) < 1) {
			return [];
		}

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$file_uuid_list = formatIn($row_list, "file_uuid");
		$query          = "SELECT * FROM `?p` WHERE `file_uuid` IN (?a) LIMIT ?i";
		$file_list      = static::_connect($sharding_key)->getAll($query, self::_getTable(), $file_uuid_list, count($file_uuid_list));

		// сортируем по created_at в порядке убывания
		usort($file_list, function(array $a, array $b) {

			return $b["created_at"] <=> $a["created_at"];
		});

		// форматируем строки в массив
		foreach ($file_list as $k => $v) {
			$file_list[$k] = self::_formatRow($v);
		}

		return $file_list;
	}

	// вставляем массив файлов в таблицу
	public static function insertArray(array $insert_array):void {

		$sharding_key = self::_getDbKey();
		static::_connect($sharding_key)->insertArray(self::_getTable(), array_map(fn (Struct_Db_CompanyConversation_ConversationFile $insert) => (array) $insert, $insert_array));
	}

	// метод для получения записи на обновление
	public static function getForUpdate(string $file_uuid):Struct_Db_CompanyConversation_ConversationFile {

		$sharding_key = self::_getDbKey();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query    = "SELECT * FROM `?p` USE INDEX (`PRIMARY`) WHERE `file_uuid` = ?s LIMIT ?i FOR UPDATE";
		$file_row = static::_connect($sharding_key)->getOne($query, self::_getTable(), $file_uuid, 1);

		return self::_formatRow($file_row);
	}

	// метод для обновления записи
	public static function set(string $file_uuid, array $set):void {

		$sharding_key = self::_getDbKey();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `file_uuid` = ?s LIMIT ?i";
		static::_connect($sharding_key)->update($query, self::_getTable(), $set, $file_uuid, 1);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	/**
	 * преобразовываем массив в структуру
	 */
	protected static function _formatRow(array $row):Struct_Db_CompanyConversation_ConversationFile {

		return new Struct_Db_CompanyConversation_ConversationFile(
			$row["file_uuid"],
			$row["row_id"],
			$row["conversation_map"],
			$row["file_map"],
			$row["file_type"],
			$row["parent_type"],
			$row["conversation_message_created_at"],
			$row["is_deleted"],
			$row["user_id"],
			$row["created_at"],
			$row["updated_at"],
			$row["parent_message_map"],
			$row["conversation_message_map"],
			fromJson($row["extra"]),
		);
	}

	// получаем таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}