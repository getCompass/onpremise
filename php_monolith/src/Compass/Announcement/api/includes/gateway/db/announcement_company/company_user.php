<?php

namespace Compass\Announcement;

/**
 * Класс-интерфейс для таблицы announcement
 */
class Gateway_Db_AnnouncementCompany_CompanyUser extends Gateway_Db_AnnouncementCompany_Main {

	protected const _TABLE_KEY        = "company_user";
	protected const _DELETE_PER_SHARD = 1000;

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @param int $company_id
	 * @param int $user_id
	 * @param int $expires_at
	 *
	 * @return Struct_Db_AnnouncementCompany_CompanyUser
	 */
	public static function insertOrUpdate(int $company_id, int $user_id, int $expires_at):Struct_Db_AnnouncementCompany_CompanyUser {

		// получаем название таблицы
		$table_name = self::_getTableName($company_id);

		$insert_row = [
			"company_id" => $company_id,
			"user_id"    => $user_id,
			"expires_at" => $expires_at,
			"created_at" => time(),
			"updated_at" => time(),
		];

		// осуществляем запрос
		self::getConnection()->insertOrUpdate($table_name, $insert_row);

		return self::_makeStructFromRow($insert_row);
	}

	/**
	 * Вставляет пачку записей в таблицу.
	 *
	 * @param array $user_company_list
	 */
	public static function insertList(array $user_company_list):void {

		$grouped_by_shard = [];

		// группируем компании по шарду
		foreach ($user_company_list as $company) {
			$grouped_by_shard[self::_getTableName($company["company_id"])][] = $company;
		}

		foreach ($grouped_by_shard as $shard => $grouped_user_company_list) {
			self::getConnection()->insertArray($shard, $grouped_user_company_list);
		}
	}

	/**
	 * метод для истёкших компаний
	 *
	 * @param string $table_shard_name
	 * @param int    $limit
	 * @param int    $offset
	 *
	 * @return array
	 */
	public static function getExpiredList(string $table_shard_name, int $limit = 20, int $offset = 0):array {

		// EXPLAIN USED KEY company_user_expires_at
		$query = "SELECT * FROM `?p` WHERE `expires_at` < ?i LIMIT ?i OFFSET ?i";

		return self::getConnection()->getAll($query, $table_shard_name, time(), $limit, $offset);
	}

	/**
	 * Получение количества записей
	 *
	 * @param string $table_shard_name
	 *
	 * @return int
	 */
	public static function getTotalCount(string $table_shard_name):int {

		// запрос проверен на EXPLAIN (INDEX=company_user_expires_at)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i";
		$row   = self::getConnection()->getOne($query, $table_shard_name, 1);
		return $row["count"];
	}

	/**
	 * Получение количества истекших записей
	 *
	 * @param string $table_shard_name
	 * @param int    $expires_at
	 *
	 * @return int
	 */
	public static function getExpiredCount(string $table_shard_name, int $expires_at):int {

		// запрос проверен на EXPLAIN (INDEX=company_user_expires_at)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `expires_at` < ?i LIMIT ?i";
		$row   = self::getConnection()->getOne($query, $table_shard_name, $expires_at, 1);
		return $row["count"];
	}

	/**
	 * Удаляем запись
	 *
	 * @param int $company_id
	 * @param int $user_id
	 */
	public static function delete(int $company_id, int $user_id):void {

		// получаем название таблицы
		$table_name = self::_getTableName($company_id);

		// EXPLAIN USED KEY PRIMARY
		$query = "DELETE FROM `?p` WHERE `company_id` = ?i AND `user_id` = ?i LIMIT ?i";
		self::getConnection()->delete($query, $table_name, $company_id, $user_id, 1);
	}

	/**
	 * Удаляем запись по company_id
	 *
	 * @param int   $user_id
	 * @param array $company_list
	 */
	public static function deleteByExceptCompanyList(int $user_id, array $company_list):void {

		$grouped_by_shard = [];

		// группируем компании по шарду
		foreach ($company_list as $company_id) {
			$grouped_by_shard[self::_getTableName($company_id)][] = $company_id;
		}

		// EXPLAIN USED KEY PRIMARY
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i AND `company_id` NOT IN (?a) LIMIT ?i";

		foreach (static::getTableShards() as $table_shard) {
			self::getConnection()->delete($query, $table_shard, $user_id, $grouped_by_shard[$table_shard] ?? [0], static::_DELETE_PER_SHARD);
		}
	}

	/**
	 * Удаляем устаревшие записи
	 *
	 * @param string $table_shard_name
	 * @param int    $expires_at
	 * @param int    $limit
	 *
	 * @return int
	 */
	public static function deleteExpired(string $table_shard_name, int $expires_at, int $limit):int {

		// запрос проверен на EXPLAIN (INDEX=company_user_expires_at)
		$query = "DELETE FROM `?p` WHERE `expires_at` < ?i LIMIT ?i";
		return self::getConnection()->delete($query, $table_shard_name, $expires_at, $limit);
	}

	/**
	 * Получаем всех известных пользователей компании.
	 *
	 * @param int $company_id
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return array
	 */
	public static function getAllUsersByCompany(int $company_id, int $limit = 1000, int $offset = 0):array {

		$shard = static::_getTableName($company_id);

		// EXPLAIN USED KEY PRIMARY
		$query  = "SELECT * FROM `?p` WHERE `company_id` = ?i LIMIT ?i OFFSET ?i";
		$result = self::getConnection()->getAll($query, $shard, $company_id, $limit, $offset);

		return array_column($result, "user_id");
	}

	/**
	 * Выполняем оптимизацию таблиц.
	 */
	public static function optimize():void {

		// доступно только из консоли
		// или для крона на серверах разработки
		if (!(isCLi() || (isCron() && isTestServer()))) {
			return;
		}

		$shard_key = self::_getDataBaseKey();

		foreach (static::getTableShards() as $table_key) {

			// EXPLAIN не требуется
			$query = "OPTIMIZE TABLE `{$shard_key}`.`{$table_key}`;";
			ShardingGateway::database($shard_key)->execQuery($query);
		}
	}

	/**
	 * Возвращает кол-во шардов таблицы
	 *
	 * @return array
	 */
	public static function getTableShards():array {

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
	 *
	 * @return Struct_Db_AnnouncementCompany_CompanyUser
	 */
	#[\JetBrains\PhpStorm\Pure]
	protected static function _makeStructFromRow(array $row):Struct_Db_AnnouncementCompany_CompanyUser {

		return new Struct_Db_AnnouncementCompany_CompanyUser(
			$row["company_id"],
			$row["user_id"],
			$row["expires_at"],
			$row["created_at"],
			$row["updated_at"]
		);
	}

	/**
	 * метод возвращает название таблицы
	 *
	 * @param int $company_id
	 *
	 * @return string
	 */
	#[\JetBrains\PhpStorm\Pure]
	protected static function _getTableName(int $company_id):string {

		return self::_TABLE_KEY . "_" . $company_id % 10;
	}
}