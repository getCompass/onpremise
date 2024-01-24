<?php

namespace Compass\Conversation;

/**
 * класс-интерфейс для таблицы message_block в company_conversation
 */
class Gateway_Db_CompanyConversation_MessageBlock extends Gateway_Db_CompanyConversation_Main {

	protected const _TABLE_KEY = "message_block";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод для создания записи
	public static function insert(string $conversation_map, array $insert):void {

		static::_connect(self::_getDbKey())->insert(self::_getTable($conversation_map), $insert);
	}

	// метод для обновления записи
	public static function set(string $conversation_map, int $block_id, array $set):void {

		static::_connect(self::_getDbKey())->update("UPDATE `?p` SET ?u WHERE conversation_map = ?s AND `block_id` = ?i LIMIT ?i",
			self::_getTable($conversation_map), $set, $conversation_map, $block_id, 1);
	}

	// метод для получения записи
	public static function getOne(string $conversation_map, int $block_id):array {

		$block_row = static::_connect(self::_getDbKey())->getOne("SELECT * FROM `?p` WHERE conversation_map = ?s AND `block_id` = ?i LIMIT ?i",
			self::_getTable($conversation_map), $conversation_map, $block_id, 1);

		$block_row["data"] = fromJson($block_row["data"]);

		return $block_row;
	}

	// метод для получения нескольких записей по первичному ключу
	public static function getList(string $conversation_map, array $in, bool $is_assoc = false):array {

		$sharding_key = self::_getDbKey();
		$query        = "SELECT * FROM `?p` WHERE conversation_map = ?s AND `block_id` IN (?a) LIMIT ?i";
		$block_list   = static::_connect($sharding_key)->getAll($query, self::_getTable($conversation_map), $conversation_map, $in, count($in));

		$output = [];
		foreach ($block_list as $k1 => $v1) {

			$v1["data"] = fromJson($v1["data"]);
			if ($is_assoc) {
				$output[$v1["block_id"]] = $v1;
			} else {
				$output[$k1] = $v1;
			}
		}

		return $output;
	}

	/**
	 * Получает N первых блоков для диалога с указанной даты.
	 */
	public static function getByPeriod(string $conversation_map, int $date_from, int $limit = 10):array {

		// EXPLAIN USE INDEX PRIMARY
		$query      = "SELECT MAX(`created_at`) as `lowest_block_date` FROM `?p` WHERE `conversation_map` = ?s AND `created_at` < ?i LIMIT ?i";
		$block_list = static::_connect(static::_getDbKey())->getOne($query, self::_getTable($conversation_map), $conversation_map, $date_from, 1);

		$date_from = $block_list["lowest_block_date"] ?? $date_from;

		// EXPLAIN USE INDEX PRIMARY
		$query      = "SELECT * FROM `?p` WHERE `conversation_map` = ?s AND `created_at` >= ?i ORDER BY `created_at` ASC LIMIT ?i";
		$block_list = static::_connect(static::_getDbKey())->getAll($query, self::_getTable($conversation_map), $conversation_map, $date_from, $limit);

		$output = [];

		foreach ($block_list as $k => $block_row) {

			$block_row["data"] = fromJson($block_row["data"]);
			$output[$k]        = $block_row;
		}

		return $output;
	}

	// метод для получения записи на обновление
	public static function getForUpdate(string $conversation_map, int $block_id):array {

		$block_row = static::_connect(self::_getDbKey())->getOne("SELECT * FROM `?p` WHERE conversation_map = ?s AND `block_id` = ?i LIMIT ?i FOR UPDATE",
			self::_getTable($conversation_map), $conversation_map, $block_id, 1);

		$block_row["data"] = fromJson($block_row["data"]);

		return $block_row;
	}

	// метод для удаления записи с блоком
	public static function delete(string $conversation_map, int $block_id):int {

		return static::_connect(self::_getDbKey())->delete("DELETE FROM `?p` WHERE conversation_map = ?s AND `block_id` = ?i LIMIT ?i",
			self::_getTable($conversation_map), $conversation_map, $block_id, 1);
	}

	/**
	 * Возвращает конкретные блоки для конкретных диалогов.
	 */
	public static function getSpecifiedList(array $block_list_by_conversation_map):array {

		$grouped_by_table = [];
		$limit            = 0;

		foreach ($block_list_by_conversation_map as $conversation_map => $block_id_list) {

			if (!isset($grouped_by_table[self::_getTable($conversation_map)])) {
				$grouped_by_table[self::_getTable($conversation_map)] = [];
			}

			$limit += count($block_id_list);
			array_push($grouped_by_table[self::_getTable($conversation_map)], $conversation_map, $block_id_list);
		}

		$output       = [];
		$sharding_key = self::_getDbKey();

		foreach ($grouped_by_table as $table_name => $table_data) {

			// EXPLAIN USE INDEX PRIMARY
			$where_expression = implode(" OR ", array_fill(0, count($table_data) / 2, "(`conversation_map` = ?s AND `block_id` IN (?a))"));
			$query            = "SELECT * FROM `?p` WHERE $where_expression LIMIT ?i";

			$block_list = static::_connect($sharding_key)->getAll(...[$query, $table_name, ...$table_data, $limit]);
			$output[]   = array_map(static fn(array $row):array => static::_decode($row), $block_list);
		}

		return array_merge(...$output);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTable(string $conversation_map):string {

		$table_id = \CompassApp\Pack\Conversation::getTableId($conversation_map);

		return self::_TABLE_KEY . "_" . $table_id;
	}

	/**
	 * Нормализует блок из сериализованной записи бд.
	 */
	protected static function _decode(array $row):array {

		$row["data"] = fromJson($row["data"]);
		return $row;
	}
}