<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс-интерфейс для таблицы company_conversation.conversation_preview
 */
class Gateway_Db_CompanyConversation_ConversationPreview extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "conversation_preview";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод для создания записи
	 *
	 * @param Struct_Db_CompanyConversation_ConversationPreview $insert
	 *
	 * @return void
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_CompanyConversation_ConversationPreview $insert):void {

		$sharding_key = self::_getDbKey();
		static::_connect($sharding_key)->insert(self::_getTable(), (array) $insert);
	}

	/**
	 * Помечаем превью удаленным по родительскому сообщению
	 *
	 * @param int   $parent_type
	 * @param array $parent_message_map_list
	 *
	 * @return void
	 */
	public static function setDeletedListByParentMapList(int $parent_type, array $parent_message_map_list):void {

		$set = [
			"is_deleted" => 1,
			"updated_at" => time(),
		];

		$sharding_key = self::_getDbKey();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `parent_type` = ?i AND `parent_message_map` IN (?a) LIMIT ?i";
		static::_connect($sharding_key)->update($query, self::_getTable(), $set, $parent_type, $parent_message_map_list, count($parent_message_map_list));
	}

	/**
	 * Получить количество записей, которые нужно удалить
	 *
	 * @param array $conversation_message_map_list
	 *
	 * @return int
	 */
	public static function getCountByConversationMessageMapList(array $conversation_message_map_list):int {

		// запрос проверен на EXPLAIN (INDEX=conversation_message_map)
		$sharding_key = self::_getDbKey();
		$query        = "SELECT COUNT(*) AS count FROM `?p` WHERE `conversation_message_map` IN (?a) LIMIT ?i";
		$row          = static::_connect($sharding_key)->getOne($query, self::_getTable(), $conversation_message_map_list, 1);

		return $row["count"];
	}

	/**
	 * Помечаем файлы удаленными по message_map чата
	 *
	 * @param array $message_map_list
	 * @param int   $count
	 *
	 * @return void
	 */
	public static function setDeletedListByConversationMessageMapList(array $message_map_list, int $count):void {

		$set = [
			"is_deleted" => 1,
			"updated_at" => time(),
		];

		// запрос проверен на EXPLAIN (INDEX=conversation_message_map)
		$sharding_key = self::_getDbKey();
		$query        = "UPDATE `?p` SET ?u WHERE `conversation_message_map` IN (?a) LIMIT ?i";
		static::_connect($sharding_key)->update($query, self::_getTable(), $set, $message_map_list, $count);
	}

	/**
	 * Получить запись на обновление
	 *
	 * @param int    $parent_type
	 * @param string $parent_message_map
	 *
	 * @return Struct_Db_CompanyConversation_ConversationPreview
	 * @throws RowNotFoundException
	 */
	public static function getForUpdate(int $parent_type, string $parent_message_map):Struct_Db_CompanyConversation_ConversationPreview {

		$sharding_key = self::_getDbKey();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `parent_type` = ?i AND `parent_message_map` = ?s LIMIT ?i FOR UPDATE";

		$preview_row = static::_connect($sharding_key)->getOne($query, self::_getTable(), $parent_type, $parent_message_map, 1);

		if (!isset($preview_row["parent_message_map"])) {
			throw new RowNotFoundException("preview not found");
		}

		return self::_formatRow($preview_row);
	}

	/**
	 * Получить список превью
	 *
	 * @param int   $parent_type
	 * @param array $parent_message_map_list
	 *
	 * @return Struct_Db_CompanyConversation_ConversationPreview[]
	 */
	public static function getList(int $parent_type, array $parent_message_map_list):array {

		$sharding_key = self::_getDbKey();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `parent_type` = ?i AND `parent_message_map` IN (?a) LIMIT ?i";

		$preview_list = static::_connect($sharding_key)->getAll($query, self::_getTable(), $parent_type, $parent_message_map_list, count($parent_message_map_list));

		return array_map(static fn(array $row) => self::_formatRow($row), $preview_list);
	}

	/**
	 * Получить список превью по сообщению в чате
	 *
	 * @param array $conversation_message_map_list
	 * @param int   $count
	 *
	 * @return array
	 */
	public static function getListByConversationMessageList(array $conversation_message_map_list, int $count):array {

		$sharding_key = self::_getDbKey();

		// запрос проверен на EXPLAIN (INDEX=get_conversation_message_map)
		$query = "SELECT * FROM `?p` WHERE `conversation_message_map` IN (?a) LIMIT ?i";

		$preview_list = static::_connect($sharding_key)->getAll($query, self::_getTable(), $conversation_message_map_list, $count);

		return array_map(static fn(array $row) => self::_formatRow($row), $preview_list);
	}

	/**
	 * метод для получения списка превью
	 *
	 * @return Struct_Db_CompanyConversation_ConversationPreview[]
	 */
	public static function getSortedList(string $conversation_map, array $parent_type_list, int $user_clear_until_at, int $count, int $offset):array {

		$sharding_key = self::_getDbKey();

		// запрос проверен на EXPLAIN (INDEX=grom_user_previews)
		$query    = "SELECT `parent_message_map` FROM `?p` FORCE INDEX (`grom_user_previews`) WHERE `conversation_map` = ?s AND `is_deleted` = ?i 
                                                                         AND `parent_type` IN (?a) AND `parent_message_created_at` > ?i 
                                                                         AND `conversation_message_created_at` > ?i 
                                                                         ORDER BY `conversation_message_created_at` DESC LIMIT ?i OFFSET ?i";
		$row_list = static::_connect($sharding_key)->getAll($query, self::_getTable(), $conversation_map, 0,
			$parent_type_list, 0, $user_clear_until_at, $count, $offset);
		if (count($row_list) < 1) {
			return [];
		}

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$parent_message_map_list = formatIn($row_list, "parent_message_map");
		$query                   = "SELECT * FROM `?p` WHERE `parent_message_map` IN (?a) LIMIT ?i";
		$preview_list            = static::_connect($sharding_key)->getAll($query, self::_getTable(), $parent_message_map_list, count($parent_message_map_list));

		// сортируем по conversation_message_created_at в порядке убывания
		usort($preview_list, function(array $a, array $b) {

			return $b["conversation_message_created_at"] <=> $a["conversation_message_created_at"];
		});

		// форматируем строки в массив
		foreach ($preview_list as $k => $v) {
			$preview_list[$k] = self::_formatRow($v);
		}

		return $preview_list;
	}

	/**
	 * метод для получения списка превью с пользователем
	 *
	 * @return Struct_Db_CompanyConversation_ConversationPreview[]
	 */
	public static function getSortedListByUserid(string $conversation_map, array $parent_type_list,
								   int    $user_clear_until_at, int $count, int $offset, int $user_id):array {

		$sharding_key = self::_getDbKey();

		// запрос проверен на EXPLAIN (INDEX=grom_user_previews)
		$query    = "SELECT `parent_message_map` FROM `?p` FORCE INDEX (`grom_user_previews`) WHERE `conversation_map` = ?s AND `is_deleted` = ?i 
                                                                         AND `parent_type` IN (?a) AND `parent_message_created_at` > ?i 
                                                                         AND `conversation_message_created_at` > ?i AND `user_id` = ?i 
                                                                         ORDER BY `conversation_message_created_at` DESC LIMIT ?i OFFSET ?i";
		$row_list = static::_connect($sharding_key)->getAll($query, self::_getTable(),
			$conversation_map, 0, $parent_type_list, 0, $user_clear_until_at, $user_id, $count, $offset);
		if (count($row_list) < 1) {
			return [];
		}

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$parent_message_map_list = formatIn($row_list, "parent_message_map");
		$query                   = "SELECT * FROM `?p` WHERE `parent_message_map` IN (?a) LIMIT ?i";
		$preview_list            = static::_connect($sharding_key)->getAll($query, self::_getTable(), $parent_message_map_list, count($parent_message_map_list));

		// сортируем по conversation_message_created_at в порядке убывания
		usort($preview_list, function(array $a, array $b) {

			return $b["conversation_message_created_at"] <=> $a["conversation_message_created_at"];
		});

		// форматируем строки в массив
		foreach ($preview_list as $k => $v) {
			$preview_list[$k] = self::_formatRow($v);
		}

		return $preview_list;
	}

	/**
	 * Метод для обновления записи
	 *
	 * @param int    $parent_type
	 * @param string $parent_message_map
	 * @param array  $set
	 *
	 * @return void
	 */
	public static function set(int $parent_type, string $parent_message_map, array $set):void {

		$sharding_key = self::_getDbKey();

		$set["updated_at"] = time();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `parent_type` = ?i AND `parent_message_map` = ?s LIMIT ?i";
		static::_connect($sharding_key)->update($query, self::_getTable(), $set, $parent_type, $parent_message_map, 1);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	/**
	 * Преобразовываем массив в структуру
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_CompanyConversation_ConversationPreview
	 */
	protected static function _formatRow(array $row):Struct_Db_CompanyConversation_ConversationPreview {

		return new Struct_Db_CompanyConversation_ConversationPreview(
			$row["parent_type"],
			$row["parent_message_map"],
			$row["is_deleted"],
			$row["conversation_message_created_at"],
			$row["parent_message_created_at"],
			$row["created_at"],
			$row["updated_at"],
			$row["user_id"],
			$row["preview_map"],
			$row["conversation_map"],
			$row["conversation_message_map"],
			fromJson($row["link_list"]),
			fromJson($row["hidden_by_user_list"])
		);
	}

	// получаем таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}