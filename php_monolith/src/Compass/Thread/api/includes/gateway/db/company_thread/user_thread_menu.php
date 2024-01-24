<?php

namespace Compass\Thread;

use JetBrains\PhpStorm\Pure;

/**
 * класс-интерфейс для таблицы cloud_user_thread.thread_menu_{ceil}
 */
class Gateway_Db_CompanyThread_UserThreadMenu extends Gateway_Db_CompanyThread_Main {

	protected const _TABLE_KEY = "user_thread_menu";

	// метод для создания записи
	public static function insert(int $user_id, string $thread_map, string $source_parent_map, int $source_parent_type, array $parent_rel):void {

		// получаем шард и таблицу
		$table_key = self::_getTableKey();

		$insert = [
			"user_id"               => $user_id,
			"thread_map"            => $thread_map,
			"source_parent_map"     => $source_parent_map,
			"source_parent_type"    => $source_parent_type,
			"is_hidden"             => 0,
			"is_follow"             => 1,
			"is_muted"              => 0,
			"unread_count"          => 0,
			"created_at"            => time(),
			"updated_at"            => time(),
			"last_read_message_map" => "",
			"parent_rel"            => $parent_rel,
		];
		ShardingGateway::database(self::_getDbKey())->insert($table_key, $insert);
	}

	// метод для создания нескольких записей
	public static function insertList(array $user_id_list, string $thread_map, string $source_parent_map, int $source_parent_type, array $parent_rel):void {

		$insert_list = [];
		foreach ($user_id_list as $user_id) {

			$insert_list[] = [
				"user_id"               => $user_id,
				"thread_map"            => $thread_map,
				"source_parent_map"     => $source_parent_map,
				"source_parent_type"    => $source_parent_type,
				"is_hidden"             => 0,
				"is_follow"             => 1,
				"is_muted"              => 0,
				"unread_count"          => 0,
				"created_at"            => time(),
				"updated_at"            => time(),
				"last_read_message_map" => "",
				"parent_rel"            => $parent_rel,
			];
		}
		ShardingGateway::database(self::_getDbKey())->insertArray(self::_getTableKey(), $insert_list);
	}

	// получение нескольких записей по массиву тредов
	public static function getList(int $user_id, array $thread_map_list):array {

		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` USE INDEX (`PRIMARY`) WHERE `user_id` = ?i AND `thread_map` IN (?a) LIMIT ?i";
		$list  = ShardingGateway::database(self::_getDbKey())->getAll($query, $table_key, $user_id, $thread_map_list, count($thread_map_list));

		foreach ($list as $k => $v) {
			$list[$k] = self::_formatRow($v);
		}

		return $list;
	}

	// получение нескольких записей по массиву пользователей
	public static function getAllByUserIdList(array $user_id_list, string $thread_map):array {

		$user_id_list_grouped_by_table_key = self::_doGroupUserIdListByTableKey($user_id_list);

		$all_list = [];
		foreach ($user_id_list_grouped_by_table_key as $table_key => $user_id_list) {

			// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
			$query = "SELECT * FROM `?p` USE INDEX (`PRIMARY`) WHERE `user_id` IN (?a) AND `thread_map` = ?s LIMIT ?i";
			$list  = ShardingGateway::database(self::_getDbKey())->getAll($query, $table_key, $user_id_list, $thread_map, count($user_id_list));

			foreach ($list as $v) {
				$all_list[] = self::_formatRow($v);
			}
		}

		return $all_list;
	}

	// получение тредов из меню
	public static function getMenu(int $user_id, int $count, int $offset):array {

		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`get_thread_menu`)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `is_hidden` = ?i ORDER BY `updated_at` DESC,`created_at` ASC LIMIT ?i OFFSET ?i";
		$list  = ShardingGateway::database(self::_getDbKey())->getAll($query, $table_key, $user_id, 0, $count, $offset);

		foreach ($list as $k => $v) {
			$list[$k] = self::_formatRow($v);
		}

		return $list;
	}

	/**
	 * Получение конкретных тредов из меню
	 *
	 * @param int   $user_id
	 * @param array $thread_map_list
	 *
	 * @return array
	 * @throws \parseException
	 */
	public static function getMenuItems(int $user_id, array $thread_map_list):array {

		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` USE INDEX (`PRIMARY`) WHERE `user_id` = ?i AND `thread_map` IN (?a) AND `is_hidden` = ?i LIMIT ?i OFFSET ?i";
		$list  = ShardingGateway::database(self::_getDbKey())->getAll($query, $table_key, $user_id, $thread_map_list, 0, count($thread_map_list), 0);

		foreach ($list as $k => $v) {
			$list[$k] = self::_formatRow($v);
		}

		return $list;
	}

	// получение непрочитанных тредов из меню
	public static function getUnreadMenu(int $user_id, int $count, int $offset):array {

		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`get_total_unread`)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `is_hidden` = ?i AND `unread_count` != ?i ORDER BY `updated_at` DESC LIMIT ?i OFFSET ?i";
		$list  = ShardingGateway::database(self::_getDbKey())->getAll($query, $table_key, $user_id, 0, 0, $count, $offset);

		foreach ($list as $k => $v) {
			$list[$k] = self::_formatRow($v);
		}

		return $list;
	}

	/**
	 * Получаем треды по флагу избранные или нет
	 */
	public static function getFavoriteMenu(int $user_id, int $count, int $offset, int $filter_favorite):array {

		$table_key = self::_getTableKey();

		$is_favorite = match ($filter_favorite) {
			-1 => 0,
			1 => 1,
		};

		// запрос проверен на EXPLAIN (INDEX=`get_favorite`)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `is_hidden` = ?i AND `is_favorite` = ?i ORDER BY `updated_at` DESC LIMIT ?i OFFSET ?i";
		$list  = ShardingGateway::database(self::_getDbKey())->getAll($query, $table_key, $user_id, 0, $is_favorite, $count, $offset);

		foreach ($list as $k => $v) {
			$list[$k] = self::_formatRow($v);
		}

		return $list;
	}

	// получить одну запись
	public static function getOne(int $user_id, string $thread_map):array {

		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `thread_map` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, $table_key, $user_id, $thread_map, 1);
		return self::_formatRow($row);
	}

	// получить одну запись
	public static function getForUpdate(int $user_id, string $thread_map):array {

		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `thread_map` = ?s LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, $table_key, $user_id, $thread_map, 1);
		return self::_formatRow($row);
	}

	// получение нескольких записей по map родителя
	public static function getListByMetaMap(int $user_id, string $source_parent_map):array {

		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT count(*) as `count` FROM `?p` WHERE `user_id` = ?i AND `source_parent_map` = ?s LIMIT ?i";
		$count = ShardingGateway::database(self::_getDbKey())->getOne($query, $table_key, $user_id, $source_parent_map, 1);

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `source_parent_map` = ?s LIMIT ?i";
		$list  = ShardingGateway::database(self::_getDbKey())->getAll($query, $table_key, $user_id, $source_parent_map, $count["count"]);

		foreach ($list as $k => $v) {
			$list[$k] = self::_formatRow($v);
		}

		return $list;
	}

	// обновление записей по map родителя и user id
	public static function setByMetaMapAndUserId(int $user_id, string $source_parent_map, int $count, array $set):void {

		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `source_parent_map` = ?s LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, $table_key, $set, $user_id, $source_parent_map, $count);
	}

	/**
	 * обновляем записи по id пользователя и списку thread_map_list
	 *
	 */
	public static function setByUserIdAndThreadMapList(int $user_id, array $thread_map_list, array $set):void {

		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `thread_map` IN (?a) LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, $table_key, $set, $user_id, $thread_map_list, count($thread_map_list));
	}

	// обновить существующую запись
	public static function set(int $user_id, string $thread_map, array $set):void {

		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `thread_map` = ?s LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, $table_key, $set, $user_id, $thread_map, 1);
	}

	// обновить существующую запись
	public static function setListWhereUserUnfollow(array $user_id_list, string $thread_map, array $set):void {

		$user_id_list_grouped_by_table_key = self::_doGroupUserIdListByTableKey($user_id_list);
		foreach ($user_id_list_grouped_by_table_key as $table_key => $user_id_list) {

			// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
			$query = "UPDATE `?p` SET ?u WHERE `user_id` IN (?a) AND `thread_map` = ?s  AND `is_follow` = ?i LIMIT ?i";
			ShardingGateway::database(self::_getDbKey())->update($query, $table_key, $set, $user_id_list, $thread_map, 0, count($user_id_list));
		}
	}

	// получение общего числа непрочитанных сообщений тредов
	public static function getTotalUnreadCounters(int $user_id):array {

		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`get_total_unread`)
		$query = "SELECT sum(`unread_count`) as `message_unread_count`, count(`thread_map`) as `thread_unread_count` FROM `?p` WHERE `user_id` = ?i AND `is_hidden` = ?i AND `unread_count` > ?i LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->getOne($query, $table_key, $user_id, 0, 0, 1);
	}

	/**
	 * Получаем число тредов в избранном у пользователя
	 */
	public static function getFavoriteCount(int $user_id):int {

		$table_key = self::_getTableKey();

		// индекс "get_favorite"
		$query = "SELECT COUNT(*) AS `count` FROM `?p` WHERE `user_id` = ?i AND `is_hidden` = ?i AND `is_favorite` = ?i LIMIT ?i";
		return ShardingGateway::database(self::_getDbKey())->getOne($query, $table_key, $user_id, 0, 1, 1)["count"];
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// группируем user_id_list по table_key
	#[Pure] protected static function _doGroupUserIdListByTableKey(array $user_id_list):array {

		$user_id_list_grouped_by_table_key = [];
		foreach ($user_id_list as $user_id) {

			$table_key                                       = self::_getTableKey();
			$user_id_list_grouped_by_table_key[$table_key][] = $user_id;
		}
		return $user_id_list_grouped_by_table_key;
	}

	// форматируем запись из базы когда её получили
	protected static function _formatRow(array $row):array {

		if (!isset($row["user_id"])) {
			return $row;
		}

		$row["parent_rel"] = fromJson($row["parent_rel"]);
		return $row;
	}
}
