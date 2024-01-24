<?php

namespace Compass\Announcement;

/**
 * Класс-интерфейс для таблицы announcement
 */
class Gateway_Db_AnnouncementMain_Announcement extends Gateway_Db_AnnouncementMain_Main {

	protected const _TABLE_KEY = "announcement";

	/**
	 * метод для создания записи
	 *
	 * @param int   $is_global
	 * @param int   $type
	 * @param int   $status
	 * @param int   $company_id
	 * @param int   $priority
	 * @param int   $expires_at
	 * @param int   $resend_repeat_time
	 * @param array $receiver_user_id_list
	 * @param array $excluded_user_id_list
	 * @param array $extra
	 *
	 * @return Struct_Db_AnnouncementMain_Announcement
	 * @throws \queryException
	 */
	public static function insert(int $is_global, int $type, int $status, int $company_id, int $priority, int $expires_at, int $resend_repeat_time, array $receiver_user_id_list, array $excluded_user_id_list, array $extra):Struct_Db_AnnouncementMain_Announcement {

		// получаем название таблицы
		$table_name = self::_getTableName();

		$insert_row = [
			"is_global"             => $is_global,
			"type"                  => $type,
			"status"                => $status,
			"company_id"            => $company_id,
			"priority"              => $priority,
			"created_at"            => time(),
			"updated_at"            => time(),
			"expires_at"            => $expires_at,
			"resend_repeat_time"    => $resend_repeat_time,
			"receiver_user_id_list" => $receiver_user_id_list,
			"excluded_user_id_list" => $excluded_user_id_list,
			"extra"                 => $extra,
		];

		// осуществляем запрос
		$announcement_id = self::getConnection()->insert($table_name, $insert_row);

		if ($announcement_id > 0) {
			$insert_row["announcement_id"] = $announcement_id;
		}

		return self::_makeStructFromRow($insert_row, false);
	}

	/**
	 * метод для обновляем записи
	 *
	 * @param int   $announcement_id
	 * @param int   $priority
	 * @param int   $expires_at
	 * @param array $receiver_user_id_list
	 * @param array $excluded_user_id_list
	 * @param array $extra
	 */
	public static function update(int $announcement_id, int $priority, int $expires_at, array $receiver_user_id_list, array $excluded_user_id_list, array $extra):void {

		// получаем название таблицы
		$table_name = self::_getTableName();

		$set = [
			"priority"              => $priority,
			"updated_at"            => time(),
			"expires_at"            => $expires_at,
			"receiver_user_id_list" => $receiver_user_id_list,
			"excluded_user_id_list" => $excluded_user_id_list,
			"extra"                 => $extra,
		];

		// EXPLAIN USED KEY PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `announcement_id` = ?i LIMIT ?i";
		self::getConnection()->update($query, $table_name, $set, $announcement_id, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @param int $announcement_id
	 *
	 * @return Struct_Db_AnnouncementMain_Announcement
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(int $announcement_id):Struct_Db_AnnouncementMain_Announcement {

		// получаем название таблицы
		$table_name = self::_getTableName();

		// EXPLAIN USED KEY PRIMARY
		$query = "SELECT * FROM `?p` WHERE `announcement_id` = ?i LIMIT ?i";

		$row = self::getConnection()->getOne($query, $table_name, $announcement_id, 1);

		if (!isset($row["announcement_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_makeStructFromRow($row);
	}

	/**
	 * метод для получения записи
	 *
	 * @param int   $type
	 * @param int   $company_id
	 * @param array $active_status_list
	 *
	 * @return Struct_Db_AnnouncementMain_Announcement
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getExistingForUpdate(int $type, int $company_id, array $active_status_list):Struct_Db_AnnouncementMain_Announcement {

		// получаем название таблицы
		$table_name = self::_getTableName();

		// EXPLAIN USED KEY get_by_type
		$query = "SELECT * FROM `?p` USE INDEX (`get_by_type`) WHERE `type` = ?i AND `company_id` = ?i AND `status` IN (?a) LIMIT ?i FOR UPDATE";
		$row   = self::getConnection()->getOne($query, $table_name, $type, $company_id, $active_status_list, 1);

		if (!isset($row["announcement_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_makeStructFromRow($row);
	}

	/**
	 * Получить все публичные-глобальные анонсы
	 *
	 * @param int   $user_id
	 * @param array $blocking_type_list
	 * @param array $active_status_list
	 * @param int   $limit
	 * @param int   $offset
	 *
	 * @return Struct_Db_AnnouncementMain_Announcement[]
	 */
	public static function getBelongsToUserPublicList(int $user_id, array $blocking_type_list, array $active_status_list, int $limit, int $offset):array {

		// EXPLAIN USED KEY get_by_type
		$query = "SELECT * FROM `?p` USE INDEX (`get_by_type`) WHERE `is_global` = ?i AND `status` IN (?a) AND `type` IN (?a) LIMIT ?i OFFSET ?i";
		$rows  = self::getConnection()->getAll(
			$query,
			self::_TABLE_KEY,
			1,
			$active_status_list,
			$blocking_type_list,
			$limit,
			$offset
		);

		$output = [];
		foreach ($rows as $row) {

			$announcement = self::_makeStructFromRow($row);

			if (!self::_isAnnouncementBelongsToUser($user_id, $announcement)) {
				continue;
			}

			$output[$row["announcement_id"]] = $announcement;
		}

		usort($output, static fn(Struct_Db_AnnouncementMain_Announcement $a, Struct_Db_AnnouncementMain_Announcement $b) => $a->priority <=> $b->priority);
		return $output;
	}

	/**
	 * Получить все глобальные и компанейские запросы принадлежащие конкретному пользователю.
	 *
	 * @param int   $user_id
	 * @param array $company_list
	 * @param array $allowed_status_list
	 * @param array $read_announcement_list
	 * @param int   $limit
	 * @param int   $offset
	 *
	 * @return Struct_Db_AnnouncementMain_Announcement[]
	 */
	public static function getBelongsToUserList(int $user_id, array $company_list, array $allowed_status_list, array $read_announcement_list, int $limit, int $offset = 0):array {

		$read_announcement_list[] = 0;
		$company_list[]           = 0;

		// EXPLAIN USED KEY get_by_status
		$query = "SELECT * FROM `?p` USE INDEX (`get_by_status`) WHERE `is_global` IN (?a) AND `status` IN (?a) AND `company_id` IN (?a) AND `announcement_id` NOT IN (?a) LIMIT ?i OFFSET ?i";
		$rows  = self::getConnection()->getAll(
			$query,
			self::_TABLE_KEY,
			[0, 1],
			$allowed_status_list,
			$company_list,
			$read_announcement_list,
			$limit,
			$offset
		);

		$output = [];

		foreach ($rows as $row) {

			$announcement = self::_makeStructFromRow($row);

			if (!self::_isAnnouncementBelongsToUser($user_id, $announcement)) {
				continue;
			}

			$output[$row["announcement_id"]] = $announcement;
		}

		usort($output, static fn(Struct_Db_AnnouncementMain_Announcement $a, Struct_Db_AnnouncementMain_Announcement $b) => $a->priority <=> $b->priority);
		return $output;
	}

	/**
	 * Получить список анонсов для отключения.
	 *
	 * @param int   $type
	 * @param int   $company_id
	 * @param array $allowed_status_list
	 * @param int   $limit
	 * @param int   $offset
	 *
	 * @return Struct_Db_AnnouncementMain_Announcement[]
	 */
	public static function getListToDisable(int $type, int $company_id, array $allowed_status_list, int $limit, int $offset = 0):array {

		// EXPLAIN USED KEY get_by_type
		$query = "SELECT * FROM `?p` USE INDEX (`get_by_type`) WHERE `status` IN (?a) AND `type` = ?i AND `company_id` = ?i  LIMIT ?i OFFSET ?i";
		$rows  = self::getConnection()->getAll(
			$query,
			self::_TABLE_KEY,
			$allowed_status_list,
			$type,
			$company_id,
			$limit,
			$offset
		);

		$output = [];

		foreach ($rows as $row) {
			$output[$row["announcement_id"]] = self::_makeStructFromRow($row);
		}

		return $output;
	}

	/**
	 * Получить список анонсов для отключения.
	 *
	 * @param int   $type
	 * @param int   $company_id
	 * @param array $allowed_status_list
	 * @param int   $limit
	 * @param int   $offset
	 *
	 * @return Struct_Db_AnnouncementMain_Announcement[]
	 */
	public static function getActiveList(int $company_id, array $allowed_status_list, int $limit, int $offset = 0):array {

		// EXPLAIN NULL, только для скрипта
		$query = "SELECT * FROM `?p` USE INDEX (`get_by_type`) WHERE `status` IN (?a) AND `company_id` = ?i  LIMIT ?i OFFSET ?i";
		$rows  = self::getConnection()->getAll(
			$query,
			self::_TABLE_KEY,
			$allowed_status_list,
			$company_id,
			$limit,
			$offset
		);

		$output = [];

		foreach ($rows as $row) {
			$output[$row["announcement_id"]] = self::_makeStructFromRow($row);
		}

		return $output;
	}

	/**
	 * Получить список анонсов для отключения.
	 *
	 * @param int   $company_id
	 * @param array $allowed_status_list
	 * @param array $type_list
	 * @param int   $limit
	 * @param int   $offset
	 *
	 * @return array
	 */
	public static function getActiveListByType(int $company_id, array $allowed_status_list, array $type_list, int $limit, int $offset = 0):array {

		// запрос проверен на EXPLAIN (INDEX='get_by_type')
		$query = "SELECT * FROM `?p` USE INDEX (`get_by_type`) WHERE `status` IN (?a) AND `type` IN (?a) AND `company_id` = ?i  LIMIT ?i OFFSET ?i";
		$rows  = self::getConnection()->getAll(
			$query,
			self::_TABLE_KEY,
			$allowed_status_list,
			$type_list,
			$company_id,
			$limit,
			$offset
		);

		$output = [];

		foreach ($rows as $row) {
			$output[$row["announcement_id"]] = self::_makeStructFromRow($row);
		}

		return $output;
	}

	/**
	 * Удаляем запись (помечаем удаленной)
	 *
	 * @param int $announcement_id
	 */
	public static function delete(int $announcement_id):void {

		// получаем название таблицы
		$table_name = self::_getTableName();

		// обновляем
		$set = [
			"status"     => \Service\AnnouncementTemplate\AnnouncementStatus::INACTIVE,
			"updated_at" => time(),
		];

		// EXPLAIN USED KEY PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `announcement_id` = ?i LIMIT ?i";
		self::getConnection()->update($query, $table_name, $set, $announcement_id, 1);
	}

	/**
	 * Получает анонсы, время истечения которых меньше указанной даты.
	 *
	 * @param int   $expires_at
	 * @param array $status_list
	 * @param int   $limit
	 * @param int   $offset
	 *
	 * @return array
	 */
	public static function getExpired(int $expires_at, array $status_list, int $limit = 100, int $offset = 0):array {

		// получаем название таблицы
		$table_name = self::_getTableName();

		// EXPLAIN USED KEY get_expired
		$query = "SELECT * FROM `?p` USE INDEX (`get_expired`) WHERE `status` IN (?a) AND `expires_at` != ?i AND `expires_at` < ?i LIMIT ?i OFFSET ?i";
		return self::getConnection()->getAll($query, $table_name, $status_list, 0, $expires_at, $limit, $offset);
	}

	/**
	 * Получение количества записей
	 *
	 * @return int
	 */
	public static function getTotalCount():int {

		// запрос проверен на EXPLAIN (INDEX=get_expired)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i";
		$row   = self::getConnection()->getOne($query, self::_getTableName(), 1);
		return $row["count"];
	}

	/**
	 * Получение количества истекших записей
	 *
	 * @param array $status_list
	 * @param int   $expires_at
	 *
	 * @return int
	 */
	public static function getExpiredCount(array $status_list, int $expires_at):int {

		// запрос проверен на EXPLAIN (INDEX=get_expired)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `status` IN (?a) AND `expires_at` != ?i AND `expires_at` < ?i LIMIT ?i";
		$row   = self::getConnection()->getOne($query, self::_getTableName(), $status_list, 0, $expires_at, 1);
		return $row["count"];
	}

	/**
	 * Выполняем оптимизацию таблиц.
	 */
	public static function optimize():void {

		// доступно только из консоли
		if (!isCLi()) {
			return;
		}

		$shard_key = self::_getDataBaseKey();
		$table_key = self::_getTableName();

		// EXPLAIN не требуется
		$query = "OPTIMIZE TABLE `{$shard_key}`.`{$table_key}`;";
		ShardingGateway::database($shard_key)->query($query);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Фильтрация для анонсов, что они предназначены для этого пользователя
	 *
	 * @param int                                     $user_id
	 * @param Struct_Db_AnnouncementMain_Announcement $announcement
	 *
	 * @return bool
	 */
	#[\JetBrains\PhpStorm\Pure]
	protected static function _isAnnouncementBelongsToUser(int $user_id, Struct_Db_AnnouncementMain_Announcement $announcement):bool {

		if (count($announcement->receiver_user_id_list) && !in_array($user_id, $announcement->receiver_user_id_list)) {
			return false;
		}

		if (count($announcement->excluded_user_id_list) && in_array($user_id, $announcement->excluded_user_id_list)) {
			return false;
		}

		return true;
	}

	/**
	 * создаем структуру из строки бд
	 *
	 * @param array $row
	 * @param bool  $is_from_db
	 *
	 * @return Struct_Db_AnnouncementMain_Announcement
	 */
	protected static function _makeStructFromRow(array $row, bool $is_from_db = true):Struct_Db_AnnouncementMain_Announcement {

		if ($is_from_db) {
			$row["receiver_user_id_list"] = fromJson($row["receiver_user_id_list"]);
			$row["excluded_user_id_list"] = fromJson($row["excluded_user_id_list"]);
			$row["extra"]                 = fromJson($row["extra"]);
		}

		return new Struct_Db_AnnouncementMain_Announcement(
			$row["announcement_id"],
			$row["is_global"],
			$row["type"],
			$row["status"],
			$row["company_id"],
			$row["priority"],
			$row["created_at"],
			$row["updated_at"],
			$row["expires_at"],
			$row["resend_repeat_time"],
			$row["receiver_user_id_list"],
			$row["excluded_user_id_list"],
			$row["extra"],
		);
	}

	/**
	 * метод возвращает название таблицы
	 *
	 * @return string
	 */
	protected static function _getTableName():string {

		return self::_TABLE_KEY;
	}
}