<?php

namespace Compass\Thread;

use CompassApp\Pack\Thread;

/**
 * класс-интерфейс для таблицы message_block в cloud_thread
 */
class Gateway_Db_CompanyThread_MessageBlock extends Gateway_Db_CompanyThread_Main {

	protected const _TABLE_KEY = "message_block";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод для создания записи
	public static function insert(string $thread_map, array $insert):void {

		ShardingGateway::database(self::_getDbKey())->insert(self::_getTable($thread_map), $insert);
	}

	// метод для обновления записи
	public static function set(string $thread_map, int $block_id, array $set):void {

		ShardingGateway::database(self::_getDbKey())->update("UPDATE `?p` SET ?u WHERE `thread_map` = ?s AND `block_id` = ?i LIMIT ?i",
			self::_getTable($thread_map), $set, $thread_map, $block_id, 1);
	}

	// метод для получения записи
	public static function getOne(string $thread_map, int $block_id):array {

		$block_row = ShardingGateway::database(self::_getDbKey())->getOne("SELECT * FROM `?p` WHERE `thread_map` = ?s AND `block_id` = ?i LIMIT ?i",
			self::_getTable($thread_map), $thread_map, $block_id, 1);

		return self::_formatRow($block_row);
	}

	// метод для получения нескольких записей по первичному ключу
	public static function getList(string $thread_map, array $in):array {

		$block_list = ShardingGateway::database(self::_getDbKey())->getAll("SELECT * FROM `?p` WHERE `thread_map` = ?s AND `block_id` IN (?a) LIMIT ?i",
			self::_getTable($thread_map), $thread_map, $in, count($in));

		foreach ($block_list as $k => $block_row) {
			$block_list[$k] = self::_formatRow($block_row);
		}

		return $block_list;
	}

	// метод для получения записи на обновление
	public static function getForUpdate(string $thread_map, int $block_id):array {

		$block_row = ShardingGateway::database(self::_getDbKey())->getOne("SELECT * FROM `?p` WHERE `thread_map` = ?s AND `block_id` = ?i LIMIT ?i FOR UPDATE",
			self::_getTable($thread_map), $thread_map, $block_id, 1);

		return self::_formatRow($block_row);
	}

	// получаем блок на обновление
	public static function getForUpdateByMessageMap(string $message_map):array {

		$thread_map = \CompassApp\Pack\Message\Thread::getThreadMap($message_map);
		$block_id   = \CompassApp\Pack\Message\Thread::getBlockId($message_map);
		return self::getForUpdate($thread_map, $block_id);
	}

	/**
	 * Возвращает конкретные блоки для конкретных тредов.
	 */
	public static function getSpecifiedList(array $block_list_by_thread_map):array {

		$grouped_by_table = [];
		$limit            = 0;

		foreach ($block_list_by_thread_map as $thread_map => $block_id_list) {

			if (!isset($grouped_by_table[self::_getTable($thread_map)])) {
				$grouped_by_table[self::_getTable($thread_map)] = [];
			}

			$limit += count($block_id_list);
			array_push($grouped_by_table[self::_getTable($thread_map)], $thread_map, $block_id_list);
		}

		$output       = [];
		$sharding_key = self::_getDbKey();

		foreach ($grouped_by_table as $table_name => $table_data) {

			// EXPLAIN USE INDEX PRIMARY
			$where_expression = implode(" OR ", array_fill(0, count($table_data) / 2, "(`thread_map` = ?s AND `block_id` IN (?a))"));
			$query            = "SELECT * FROM `?p` WHERE $where_expression LIMIT ?i";

			$block_list = ShardingGateway::database($sharding_key)->getAll(...[$query, $table_name, ...$table_data, $limit]);
			$output[]   = array_map(static fn(array $row):array => static::_formatRow($row), $block_list);
		}

		return array_merge(...$output);
	}

	// метод для получения нескольких записей по первичному ключу для обновления
	public static function getListForUpdate(string $thread_map, array $in):array {

		$block_list = ShardingGateway::database(self::_getDbKey())->getAll("SELECT * FROM `?p` WHERE `thread_map` = ?s AND `block_id` IN (?a) LIMIT ?i FOR UPDATE",
			self::_getTable($thread_map), $thread_map, $in, count($in));

		foreach ($block_list as $k => $block_row) {
			$block_list[$k] = self::_formatRow($block_row);
		}

		return $block_list;
	}

	// метод для удаления нескольких записей
	public static function delete(string $thread_map, int $block_id):int {

		return ShardingGateway::database(self::_getDbKey())->delete("DELETE FROM `?p` WHERE `thread_map` = ?s AND `block_id` = ?i LIMIT ?i",
			self::_getTable($thread_map), $thread_map, $block_id, 1);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// приводим к формату запись из таблицы
	protected static function _formatRow(array $block_row):array {

		$block_row["data"] = fromJson($block_row["data"]);

		return $block_row;
	}

	// получаем таблицу
	protected static function _getTable(string $thread_map):string {

		$table_id = Thread::getTableId($thread_map);

		return self::_TABLE_KEY . "_" . $table_id;
	}
}