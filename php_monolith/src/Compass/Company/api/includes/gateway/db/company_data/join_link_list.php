<?php

namespace Compass\Company;

/**
 * Класс-интерфейс для таблицы company_data.join_link_list
 */
class Gateway_Db_CompanyData_JoinLinkList extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "join_link_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 */
	public static function insert(
		string $join_link_uniq,
		int    $entry_option,
		int    $status,
		int    $type,
		int    $expires_at,
		int    $can_use_count,
		int    $creator_user_id,
		int    $created_at,
		int    $updated_at
	):Struct_Db_CompanyData_JoinLink {

		$insert = [
			"join_link_uniq"  => $join_link_uniq,
			"entry_option"    => $entry_option,
			"status"          => $status,
			"type"            => $type,
			"can_use_count"   => $can_use_count,
			"expires_at"      => $expires_at,
			"creator_user_id" => $creator_user_id,
			"created_at"      => $created_at,
			"updated_at"      => $updated_at,
		];
		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert);

		// осуществляем запрос
		return self::_formatRow($insert);
	}

	/**
	 * метод для обновления записи
	 */
	public static function set(string $join_link_uniq, array $set):void {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `join_link_uniq` = ?s LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $join_link_uniq, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(string $join_link_uniq):Struct_Db_CompanyData_JoinLink {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `join_link_uniq` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $join_link_uniq, 1);

		if (!isset($row["join_link_uniq"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_formatRow($row);
	}

	/**
	 * метод для получения записи на обновление
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getForUpdate(string $join_link_uniq):Struct_Db_CompanyData_JoinLink {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `join_link_uniq` = ?s LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $join_link_uniq, 1);
		if (!isset($row["join_link_uniq"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_formatRow($row);
	}

	/**
	 * Метод для получения массива записей
	 */
	public static function getCountByTypeAndStatus(array $type_list, int $status, int $time):int {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=get_by_type_and_status)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `type` IN (?a) AND `status` = ?i AND (`expires_at` > ?i OR `expires_at` = ?i) LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $type_list, $status, $time, 0, 1);

		return $row["count"];
	}

	/**
	 * Метод для получения массива ссылок по статусу и типу
	 */
	public static function getByTypeAndStatus(array $type_list, int $status, int $time, int $limit):array {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=get_by_type_and_status)
		$query = "SELECT * FROM `?p` FORCE INDEX(`get_by_type_and_status`) WHERE `type` IN (?a) AND `status` = ?i AND (`expires_at` > ?i OR `expires_at` = ?i) LIMIT ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $type_list, $status, $time, 0, $limit);

		$list = [];
		foreach ($rows as $row) {
			$list[] = self::_formatRow($row);
		}

		return $list;
	}

	/**
	 * Метод для получения активных инвайтов
	 */
	public static function getCountActiveList(int $status, int $time):int {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=get_status_expires)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `status` = ?i AND `expires_at` > ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $status, $time, 1);

		return $row["count"];
	}

	/**
	 * Метод для получения активных инвайтов
	 */
	public static function getActiveList(int $status, int $time, int $limit):array {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=get_status_expires)
		$query = "SELECT * FROM `?p` WHERE `status` = ?i AND `expires_at` > ?i LIMIT ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $status, $time, $limit);

		$list = [];
		foreach ($rows as $row) {
			$list[] = self::_formatRow($row);
		}

		return $list;
	}

	/**
	 * Метод для получения неактивных инвайтов
	 *
	 * @return Struct_Db_CompanyData_JoinLink[]
	 */
	public static function getInactiveList(int $active_status, int $used_status, int $time, int $last_expires_at, int $limit, int $count):array {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=get_status_expires)
		$query = "SELECT * FROM `?p` FORCE INDEX(`get_status_expires`) 
			WHERE `status` = ?i OR (`status` = ?i AND `expires_at` < ?i) AND `expires_at` > ?i
			ORDER BY `updated_at` DESC LIMIT ?i OFFSET ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $used_status, $active_status, $time, $last_expires_at, $limit, $count);

		$list = [];
		foreach ($rows as $row) {
			$list[] = self::_formatRow($row);
		}

		return $list;
	}

	/**
	 * Метод для получения неактивных инвайтов по типу
	 *
	 * @return Struct_Db_CompanyData_JoinLink[]
	 */
	public static function getInactiveListByType(array $type_list, int $active_status, int $used_status, int $time, int $last_expires_at, int $limit, int $count):array {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=get_status_expires)
		$query = "SELECT * FROM `?p` FORCE INDEX(`get_status_expires`) 
			WHERE `type` IN (?a) AND `status` = ?i OR (`status` = ?i AND `expires_at` < ?i) AND `expires_at` > ?i
			ORDER BY `updated_at` DESC LIMIT ?i OFFSET ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $type_list, $used_status, $active_status, $time, $last_expires_at, $limit, $count);

		$list = [];
		foreach ($rows as $row) {
			$list[] = self::_formatRow($row);
		}

		return $list;
	}

	/**
	 * Метод для получения массива записей
	 *
	 * @return Struct_Db_CompanyData_JoinLink[]
	 */
	public static function getList(array $status_list, int $limit):array {

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE `status` IN (?a) ORDER BY `updated_at` DESC LIMIT ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $status_list, $limit);

		$list = [];
		foreach ($rows as $row) {
			$list[] = self::_formatRow($row);
		}

		return $list;
	}

	/**
	 * Метод для получения количества записей
	 */
	public static function getCountAllByUserId(int $creator_user_id, int $status):int {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=get_created_by_user_id_and_status)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `creator_user_id` = ?i AND `status` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $creator_user_id, $status, 1);

		return $row["count"];
	}

	/**
	 * Метод для получения массива записей
	 *
	 * @return Struct_Db_CompanyData_JoinLink[]
	 */
	public static function getAllByUserId(int $creator_user_id, int $status, int $limit):array {

		// запрос проверен на EXPLAIN (INDEX=get_created_by_user_id_and_status)
		$query = "SELECT * FROM `?p` WHERE `creator_user_id` = ?i AND `status` = ?i LIMIT ?i";
		$rows  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $creator_user_id, $status, $limit);

		$list = [];
		foreach ($rows as $row) {
			$list[] = self::_formatRow($row);
		}

		return $list;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * преобразовываем массив в структуру
	 */
	protected static function _formatRow(array $row):Struct_Db_CompanyData_JoinLink {

		return new Struct_Db_CompanyData_JoinLink(
			$row["join_link_uniq"],
			$row["entry_option"],
			$row["status"],
			$row["type"],
			$row["can_use_count"],
			$row["expires_at"],
			$row["creator_user_id"],
			$row["created_at"],
			$row["updated_at"],
		);
	}
}
