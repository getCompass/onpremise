<?php

namespace Compass\Announcement;

/**
 * Класс-интерфейс для таблицы announcement
 */
class Gateway_Db_AnnouncementUser_UserAnnouncement extends Gateway_Db_AnnouncementUser_Main {

	protected const _TABLE_KEY = "user_announcement";

	/**
	 * метод для создания записи
	 *
	 * @param int   $announcement_id
	 * @param int   $user_id
	 * @param int   $is_read
	 * @param int   $next_resend_at
	 * @param array $extra
	 *
	 * @return Struct_Db_AnnouncementUser_UserAnnouncement
	 */
	public static function insert(int $announcement_id, int $user_id, int $is_read, int $next_resend_at, array $extra):Struct_Db_AnnouncementUser_UserAnnouncement {

		// получаем название таблицы
		$table_name = self::_getTableName($user_id);
		$insert     = [
			"announcement_id"     => $announcement_id,
			"user_id"             => $user_id,
			"is_read"             => $is_read,
			"created_at"          => time(),
			"updated_at"          => time(),
			"next_resend_at"      => $next_resend_at,
			"resend_attempted_at" => 0,
			"extra"               => $extra,
		];

		// осуществляем запрос
		self::getConnection(static::_getShardSuffix($user_id))->insertOrUpdate($table_name, $insert);
		return self::_makeStructFromRow($insert, false);
	}

	/**
	 * Выполняет обновление связи пользователь-анонс.
	 *
	 * @param int   $announcement_id
	 * @param int   $user_id
	 * @param array $set
	 */
	public static function update(int $announcement_id, int $user_id, array $set):void {

		// получаем название таблицы
		$table_name = self::_getTableName($user_id);

		// EXPLAIN USED KEY PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `announcement_id` = ?i AND `user_id` = ?i LIMIT ?i";
		self::getConnection(static::_getShardSuffix($user_id))->update($query, $table_name, $set, $announcement_id, $user_id, 1);
	}

	/**
	 * Получить одну запись
	 *
	 * @param int $announcement_id
	 * @param int $user_id
	 *
	 * @return Struct_Db_AnnouncementUser_UserAnnouncement
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(int $announcement_id, int $user_id):Struct_Db_AnnouncementUser_UserAnnouncement {

		// получаем название таблицы
		$table_name = self::_getTableName($user_id);

		// EXPLAIN USED KEY PRIMARY
		$query = "SELECT * FROM `?p` WHERE `announcement_id` = ?i AND `user_id` = ?i LIMIT ?i";
		$row   = self::getConnection(static::_getShardSuffix($user_id))->getOne($query, $table_name, $announcement_id, $user_id, 1);

		if (count($row) === 0) {
			throw new \cs_RowIsEmpty();
		}

		return self::_makeStructFromRow($row);
	}

	/**
	 * Получение количества записей
	 *
	 * @param string $table_shard_name
	 *
	 * @return int
	 */
	public static function getTotalCount(string $table_shard_name):int {

		// запрос проверен на EXPLAIN (INDEX=user_announcement_need_resend)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i";
		$row   = self::getConnection(static::_getShardSuffix(1))->getOne($query, $table_shard_name, 1);
		return $row["count"];
	}

	/**
	 * Получение количества истекших записей
	 *
	 * @param string $table_shard_name
	 * @param int    $next_resend_at
	 *
	 * @return int
	 */
	public static function getExpiredCount(string $table_shard_name, int $next_resend_at):int {

		// запрос проверен на EXPLAIN (INDEX=user_announcement_need_resend)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `next_resend_at` <= ?i AND `resend_attempted_at` = ?i LIMIT ?i";
		$row   = self::getConnection(static::_getShardSuffix(1))->getOne($query, $table_shard_name, $next_resend_at, 0, 1);
		return $row["count"];
	}

	/**
	 * Получить список анонсов для пользователя.
	 *
	 * @param int $user_id
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return array
	 */
	public static function getAnnouncementIdListByUserId(int $user_id, int $limit, int $offset):array {

		// получаем название таблицы
		$table_name = self::_getTableName($user_id);

		// EXPLAIN USED KEY user_announcement_is_read
		$query = "SELECT `announcement_id` FROM `?p` WHERE `user_id` = ?i AND `is_read` = ?i AND (`next_resend_at` = ?i OR `next_resend_at` > ?i) LIMIT ?i OFFSET ?i";
		return self::getConnection(static::_getShardSuffix($user_id))->getAllColumn($query, $table_name, $user_id, 1, 0, time(), $limit, $offset);
	}

	/**
	 * Получает id'шники всех прочитанных анонсов
	 *
	 * @param int $user_id
	 * @param int $batch_size
	 *
	 * @return array
	 */
	public static function getAllAnnouncementIdListByUserId(int $user_id, int $batch_size = 500):array {

		$offset                 = 0;
		$read_announcement_list = [];

		do {

			$rows                   = Gateway_Db_AnnouncementUser_UserAnnouncement::getAnnouncementIdListByUserId($user_id, $batch_size, $offset);
			$read_announcement_list = array_merge($read_announcement_list, $rows);
			$offset                 += $batch_size;
		} while (count($rows) === $batch_size);

		return $read_announcement_list;
	}

	/**
	 * Получает все записи для пересылки.
	 *
	 * @param string $table_shard
	 * @param int    $timestamp
	 * @param int    $limit
	 * @param int    $offset
	 *
	 * @return Struct_Db_AnnouncementUser_UserAnnouncement[]
	 */
	public static function getToResend(string $table_shard, int $timestamp, int $limit, int $offset):array {

		// EXPLAIN USED KEY user_announcement_need_resend
		$query  = "SELECT * FROM `?p` USE INDEX (`user_announcement_need_resend`) WHERE `next_resend_at` <= ?i AND `resend_attempted_at` = ?i LIMIT ?i OFFSET ?i";
		$result = self::getConnection(static::_getShardSuffix(1))->getAll($query, $table_shard, $timestamp, 0, $limit, $offset);

		return array_map(fn(array $el) => self::_makeStructFromRow($el), $result);
	}

	/**
	 * Получает все пользователей для пересылки
	 *
	 * @param string $table_shard
	 * @param int    $announcement_id
	 * @param int    $timestamp
	 * @param int    $limit
	 * @param int    $offset
	 *
	 * @return array
	 */
	public static function getAnnouncementResendReceivers(string $table_shard, int $announcement_id, int $timestamp, int $limit, int $offset):array {

		// EXPLAIN USED KEY PRIMARY
		$query = "SELECT `user_id` FROM `?p` WHERE `announcement_id` = ?i AND (`next_resend_at` = ?i OR `next_resend_at` >= ?i) LIMIT ?i OFFSET ?i";
		return self::getConnection(static::_getShardSuffix(1))->getAllColumn($query, $table_shard, $announcement_id, 0, $timestamp, $limit, $offset);
	}

	/**
	 * Обновить данные о следующем запросе всем пользователям для указанного анонса.
	 *
	 * @param int   $announcement_id
	 * @param array $user_id_list
	 * @param int   $resend_attempted_at
	 * @param int   $next_resend_at
	 */
	public static function updateNextResendAttemptedAt(int $announcement_id, array $user_id_list, int $resend_attempted_at, int $next_resend_at = 0):void {

		$grouped_by_shard = [];

		// группируем компании по шарду
		foreach ($user_id_list as $user_id) {
			$grouped_by_shard[self::_getTableName($user_id)][] = $user_id;
		}

		$set = [
			"updated_at"          => time(),
			"resend_attempted_at" => $resend_attempted_at,
		];

		if ($next_resend_at != 0) {
			$set["next_resend_at"] = $next_resend_at;
		}

		foreach (static::_getAllDatabaseShards() as $db_shard_suffix) {

			foreach ($grouped_by_shard as $shard => $grouped_user_id_list) {

				// EXPLAIN USED KEY PRIMARY
				$query = "UPDATE `?p` SET ?u WHERE announcement_id = ?i AND user_id IN (?a) LIMIT ?i";
				self::getConnection($db_shard_suffix)->update($query, $shard, $set, $announcement_id, $grouped_user_id_list, count($grouped_user_id_list));
			}
		}
	}

	/**
	 * Метод возвращает все возможные шарды таблицы.
	 *
	 * @return array
	 */
	public static function getAllTableShards():array {

		$output = [];

		for ($i = 0; $i < 10; $i++) {
			$output[] = self::_TABLE_KEY . "_" . $i;
		}

		return $output;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * создаем структуру из строки бд
	 *
	 * @param array $row
	 * @param bool  $is_from_db
	 *
	 * @return Struct_Db_AnnouncementUser_UserAnnouncement
	 */
	protected static function _makeStructFromRow(array $row, bool $is_from_db = true):Struct_Db_AnnouncementUser_UserAnnouncement {

		if ($is_from_db) {
			$row["extra"] = fromJson($row["extra"]);
		}

		return new Struct_Db_AnnouncementUser_UserAnnouncement(
			$row["announcement_id"],
			$row["user_id"],
			$row["is_read"],
			$row["created_at"],
			$row["updated_at"],
			$row["next_resend_at"],
			$row["resend_attempted_at"],
			$row["extra"],
		);
	}

	/**
	 * метод возвращает название таблицы
	 *
	 * @param int $user_id
	 *
	 * @return string
	 */
	protected static function _getTableName(int $user_id):string {

		return self::_TABLE_KEY . "_" . $user_id % 10;
	}

}