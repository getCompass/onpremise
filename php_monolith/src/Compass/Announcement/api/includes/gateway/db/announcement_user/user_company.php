<?php

namespace Compass\Announcement;

/**
 * Класс-интерфейс для таблицы announcement
 */
class Gateway_Db_AnnouncementUser_UserCompany extends Gateway_Db_AnnouncementUser_Main {

	protected const _TABLE_KEY = "user_company";

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
	 * @return Struct_Db_AnnouncementUser_UserCompany
	 */
	public static function insertOrUpdate(int $user_id, int $company_id, int $expires_at):Struct_Db_AnnouncementUser_UserCompany {

		// получаем название таблицы
		$table_name = self::_getTableName($user_id);

		$insert_row = [
			"user_id"    => $user_id,
			"company_id" => $company_id,
			"expires_at" => $expires_at,
			"created_at" => time(),
			"updated_at" => time(),
		];

		// осуществляем запрос
		self::getConnection(static::_getShardSuffix($user_id))->insertOrUpdate($table_name, $insert_row);

		return self::_makeStructFromRow($insert_row);
	}

	/**
	 * метод для создания нескольких записей
	 *
	 * @param array $user_company_list
	 */
	public static function insertList(array $user_company_list):void {

		$grouped_by_shard = [];

		// группируем компании по шарду
		foreach ($user_company_list as $company) {
			$grouped_by_shard[self::_getTableName($company["user_id"])][] = $company;
		}

		foreach ($grouped_by_shard as $shard => $grouped_user_company_list) {
			self::getConnection(static::_getShardSuffix(1))->insertArray($shard, $grouped_user_company_list);
		}
	}

	/**
	 * Получить список по указанному id пользователя.
	 *
	 * @param int $user_id
	 * @param int $limit
	 * @param int $offset
	 *
	 * @return array
	 */
	public static function getListByUserId(int $user_id, int $limit, int $offset):array {

		// получаем название таблицы
		$table_name = self::_getTableName($user_id);

		// EXPLAIN USED KEY PRIMARY
		$query = "SELECT `company_id` FROM `?p` WHERE `user_id` = ?i LIMIT ?i OFFSET ?i";
		return self::getConnection(static::_getShardSuffix($user_id))->getAllColumn($query, $table_name, $user_id, $limit, $offset);
	}

	/**
	 * Получает id'шники всех компаний пользователя
	 *
	 * @param int $user_id
	 * @param int $batch_size
	 *
	 * @return array
	 */
	public static function getAllCompanyIdByUserId(int $user_id, int $batch_size = 500):array {

		$offset       = 0;
		$company_list = [];

		do {
			$rows         = self::getListByUserId($user_id, $batch_size, $offset);
			$company_list = array_merge($company_list, $rows);
			$offset       += $batch_size;
		} while (count($rows) === $batch_size);

		return $company_list;
	}

	/**
	 * метод для истёкших компаний
	 *
	 * @param int    $database_shard
	 * @param string $table_shard_name
	 * @param int    $limit
	 * @param int    $offset
	 *
	 * @return array
	 */
	public static function getExpiredList(int $database_shard, string $table_shard_name, int $limit = 20, int $offset = 0):array {

		// EXPLAIN USED KEY user_company_expires_at
		$query = "SELECT * FROM `?p` WHERE `expires_at` < ?i LIMIT ?i OFFSET ?i";
		return self::getConnection(static::_getShardSuffix($database_shard))->getAll($query, $table_shard_name, time(), $limit, $offset);
	}

	/**
	 * Получение количества записей
	 *
	 * @param string $table_shard_name
	 *
	 * @return int
	 */
	public static function getTotalCount(string $table_shard_name):int {

		// запрос проверен на EXPLAIN (INDEX=user_company_expires_at)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i";
		$row   = self::getConnection(static::_getShardSuffix(1))->getOne($query, $table_shard_name, 1);
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

		// запрос проверен на EXPLAIN (INDEX=user_company_expires_at)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `expires_at` < ?i LIMIT ?i";
		$row   = self::getConnection(static::_getShardSuffix(1))->getOne($query, $table_shard_name, $expires_at, 1);
		return $row["count"];
	}

	/**
	 * Удаляем запись
	 *
	 * @param int $user_id
	 * @param int $company_id
	 */
	public static function delete(int $user_id, int $company_id):void {

		// получаем название таблицы
		$table_name = self::_getTableName($user_id);

		// EXPLAIN USED KEY PRIMARY
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i AND `company_id` = ?i LIMIT ?i";
		self::getConnection(static::_getShardSuffix($user_id))->delete($query, $table_name, $user_id, $company_id, 1);
	}

	/**
	 * Удаляем запись по company_id
	 *
	 * @param int   $user_id
	 * @param array $company_list
	 */
	public static function deleteByExceptCompanyList(int $user_id, array $company_list):void {

		// получаем название таблицы
		$table_name = self::_getTableName($user_id);

		// EXPLAIN USED KEY PRIMARY
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i AND `company_id` NOT IN (?a) LIMIT ?i";
		self::getConnection(static::_getShardSuffix($user_id))->delete($query, $table_name, $user_id, $company_list, count($company_list));
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

		// запрос проверен на EXPLAIN (INDEX=user_company_expires_at)
		$query = "DELETE FROM `?p` WHERE `expires_at` < ?i LIMIT ?i";
		return self::getConnection(static::_getShardSuffix(1))->delete($query, $table_shard_name, $expires_at, $limit);
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

		foreach (static::_getAllDatabaseShards() as $shard_suffix) {

			foreach (static::getTableShards() as $table_key) {

				$shard_key = static::_DB_KEY . "_$shard_suffix";

				// EXPLAIN не требуется
				$query = "OPTIMIZE TABLE `{$shard_key}`.`{$table_key}`;";
				ShardingGateway::database($shard_key)->execQuery($query);
			}
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
	 * @return Struct_Db_AnnouncementUser_UserCompany
	 */
	#[\JetBrains\PhpStorm\Pure]
	protected static function _makeStructFromRow(array $row):Struct_Db_AnnouncementUser_UserCompany {

		return new Struct_Db_AnnouncementUser_UserCompany(
			$row["user_id"],
			$row["company_id"],
			$row["expires_at"],
			$row["created_at"],
			$row["updated_at"]
		);
	}

	/**
	 * метод возвращает название таблицы
	 *
	 * @param int $user_id
	 *
	 * @return string
	 */
	#[\JetBrains\PhpStorm\Pure]
	protected static function _getTableName(int $user_id):string {

		return self::_TABLE_KEY . "_" . $user_id % 10;
	}
}