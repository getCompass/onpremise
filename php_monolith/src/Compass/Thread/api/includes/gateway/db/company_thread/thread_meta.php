<?php

namespace Compass\Thread;

use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс-интерфейс к таблице cloud_thread_{Year}.meta
 */
class Gateway_Db_CompanyThread_ThreadMeta extends Gateway_Db_CompanyThread_Main {

	protected const _TABLE_KEY = "thread_meta";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод для создания записи
	// принимает в качестве параметров не привычный thread_map как в других методах класса
	// потому что на момент insert thread_map не сформирован (не хватает meta_id, который возвращает функция)
	public static function insert(int $meta_id, int $year, int $is_private, int $created_at, int $creator_user_id, array $users, array $source_parent_rel, array $parent_rel):array {

		$insert = [
			"meta_id"           => $meta_id,
			"year"              => $year,
			"is_private"        => $is_private,
			"is_mono"           => 0,
			"is_readonly"       => 0,
			"created_at"        => $created_at,
			"updated_at"        => 0,
			"message_count"     => 0,
			"creator_user_id"   => $creator_user_id,
			"users"             => $users,
			"source_parent_rel" => $source_parent_rel,
			"parent_rel"        => $parent_rel,
			"sender_order"      => [],
			"last_sender_data"  => [],
		];

		ShardingGateway::database(self::_getDbKey())->insert(self::_getTableKey(), $insert);

		return $insert;
	}

	// метод для обновления записи
	public static function set(string $thread_map, array $set):void {

		$meta_id = \CompassApp\Pack\Thread::getMetaId($thread_map);
		$year    = \CompassApp\Pack\Thread::getShardId($thread_map);

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE `meta_id` = ?i AND `year` = ?i LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_getTableKey(), $set, $meta_id, $year, 1);
	}

	// метод для получения записи
	public static function getOne(string $thread_map):array {

		$meta_id = \CompassApp\Pack\Thread::getMetaId($thread_map);
		$year    = \CompassApp\Pack\Thread::getShardId($thread_map);

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `meta_id` = ?i AND `year` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_getTableKey(), $meta_id, $year, 1);

		if (count($row) === 0) {
			throw new RowNotFoundException("thread not found");
		}

		return self::_formatRow($thread_map, $row);
	}

	// получаем все записи
	// @long
	public static function getAll(array $thread_map_list):array {

		$table_key = self::_getTableKey();

		$meta_year_list     = [];
		$thread_meta_list   = [];
		$thread_meta_output = [];
		$meta_map_list      = [];
		foreach ($thread_map_list as $v) {
			$meta_year_list[\CompassApp\Pack\Thread::getShardId($v)][]                                             = \CompassApp\Pack\Thread::getMetaId($v);
			$meta_map_list[\CompassApp\Pack\Thread::getMetaId($v) . "_" . \CompassApp\Pack\Thread::getShardId($v)] = $v;
		}

		foreach ($meta_year_list as $year => $full_meta_list) {

			// разбиваем на чанки по 300
			$chunk_meta_list = array_chunk($full_meta_list, 300);
			foreach ($chunk_meta_list as $meta_list) {

				// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
				$query            = "SELECT * FROM `?p` WHERE `meta_id` IN (?a) AND `year` = ?i LIMIT ?i";
				$thread_meta_list = array_merge($thread_meta_list, ShardingGateway::database(self::_getDbKey())->getAll(
					$query,
					$table_key,
					$meta_list,
					$year,
					count($meta_list)
				));
			}
		}

		foreach ($thread_meta_list as $thread_meta) {
			$thread_meta_output[] = self::_formatRow($meta_map_list[$thread_meta["meta_id"] . "_" . $thread_meta["year"]], $thread_meta);
		}

		return $thread_meta_output;
	}

	/**
	 * получаем все записи
	 *
	 */
	public static function getAllWhereCreator(int $creator_user_id, array $thread_map_list):array {

		$table_key = self::_getTableKey();

		$meta_year_list     = [];
		$thread_meta_list   = [];
		$thread_meta_output = [];
		$meta_map_list      = [];
		foreach ($thread_map_list as $v) {

			$meta_year_list[\CompassApp\Pack\Thread::getShardId($v)][]                                             = \CompassApp\Pack\Thread::getMetaId($v);
			$meta_map_list[\CompassApp\Pack\Thread::getMetaId($v) . "_" . \CompassApp\Pack\Thread::getShardId($v)] = $v;
		}

		foreach ($meta_year_list as $year => $meta_list) {

			// запрос проверен на EXPLAIN (INDEX=`get_by_meta_id_year_creator_user_id`)
			$query            = "SELECT * FROM `?p` FORCE INDEX (`get_by_meta_id_year_creator_user_id`) WHERE `meta_id` IN (?a) AND `year` = ?i AND `creator_user_id` = ?i LIMIT ?i";
			$thread_meta_list = array_merge($thread_meta_list, ShardingGateway::database(self::_getDbKey())->getAll(
				$query,
				$table_key,
				$meta_list,
				$year,
				$creator_user_id,
				count($meta_list)
			));
		}

		foreach ($thread_meta_list as $thread_meta) {
			$thread_meta_output[] = self::_formatRow($meta_map_list[$thread_meta["meta_id"] . "_" . $thread_meta["year"]], $thread_meta);
		}

		return $thread_meta_output;
	}

	// метод для обновления записей
	public static function setAll(array $thread_map_list, array $set):void {

		$table_key      = self::_getTableKey();
		$meta_year_list = [];
		foreach ($thread_map_list as $v) {

			$meta_year_list[\CompassApp\Pack\Thread::getShardId($v)][] = \CompassApp\Pack\Thread::getMetaId($v);
		}

		foreach ($meta_year_list as $year => $meta_list) {

			// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
			$query = "UPDATE `?p` SET ?u WHERE `meta_id` IN (?a) AND `year` = ?i LIMIT ?i";
			ShardingGateway::database(self::_getDbKey())->update(
				$query,
				$table_key,
				$set,
				$meta_list,
				$year,
				count($meta_list)
			);
		}
	}

	// метод для получения записи на обновление
	public static function getForUpdate(string $thread_map):array {

		$meta_id  = \CompassApp\Pack\Thread::getMetaId($thread_map);
		$shard_id = \CompassApp\Pack\Thread::getShardId($thread_map);

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `meta_id` = ?i AND `year` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_getTableKey(), $meta_id, $shard_id, 1);
		return self::_formatRow($thread_map, $row);
	}

	// обновляем мету треда при добавлении сообщении
	public static function updateThreadMetaOnMessageAdd(string $thread_map, int $user_id, int $message_count = 1):array {

		// получаем мету на обновление
		$meta_row = self::getForUpdate($thread_map);

		// обновляем значения
		$meta_row["message_count"]    += $message_count;
		$meta_row["updated_at"]       = time();
		$meta_row["sender_order"][]   = $user_id;
		$meta_row["last_sender_data"] = Type_Thread_Meta_LastSenderData::addNewItem($meta_row["last_sender_data"], $meta_row["message_count"], $user_id);

		// обновляем
		$set = [
			"message_count"    => $meta_row["message_count"],
			"updated_at"       => $meta_row["updated_at"],
			"sender_order"     => $meta_row["sender_order"],
			"last_sender_data" => $meta_row["last_sender_data"],
		];
		self::set($thread_map, $set);
		return $meta_row;
	}

	// обновляем мету треда при системном удалении сообщения
	public static function updateThreadMetaOnMessageSystemDeleted(string $thread_map, string $message_index):array {

		// получаем мету на обновление
		$meta_row = self::getForUpdate($thread_map);

		// обновляем значения
		$meta_row["message_count"]    -= 1;
		$thread_message_index         = \CompassApp\Pack\Message\Thread::getThreadMessageIndex($message_index);
		$meta_row["last_sender_data"] = Type_Thread_Meta_LastSenderData::removeItem($meta_row["last_sender_data"], $thread_message_index);
		$meta_row["updated_at"]       = time();

		// обновляем
		$set = [
			"message_count"    => $meta_row["message_count"],
			"last_sender_data" => $meta_row["last_sender_data"],
			"updated_at"       => $meta_row["updated_at"],
		];
		self::set($thread_map, $set);
		return $meta_row;
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// форматируем запись после того как получили её из базы
	protected static function _formatRow(string $thread_map, array $meta_row):array {

		$meta_row["thread_map"] = $thread_map;
		unset($meta_row["meta_id"]);
		unset($meta_row["year"]);

		$meta_row["users"]             = fromJson($meta_row["users"]);
		$meta_row["source_parent_rel"] = fromJson($meta_row["source_parent_rel"]);
		$meta_row["parent_rel"]        = fromJson($meta_row["parent_rel"]);
		$meta_row["sender_order"]      = fromJson($meta_row["sender_order"]);
		$meta_row["last_sender_data"]  = fromJson($meta_row["last_sender_data"]);

		return $meta_row;
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}
}
