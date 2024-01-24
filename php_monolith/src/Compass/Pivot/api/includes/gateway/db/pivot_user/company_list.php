<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_user_{10m}.company_list_{1}
 */
class Gateway_Db_PivotUser_CompanyList extends Gateway_Db_PivotUser_Main {

	protected const _TABLE_KEY = "company_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotUser_Company $user_company):string {

		$shard_key  = self::_getDbKey($user_company->user_id);
		$table_name = self::_getTableKey($user_company->user_id);

		$insert = [
			"user_id"    => $user_company->user_id,
			"company_id" => $user_company->company_id,
			"is_has_pin" => $user_company->is_has_pin,
			"order"      => $user_company->order,
			"entry_id"   => $user_company->entry_id,
			"created_at" => $user_company->created_at,
			"updated_at" => $user_company->updated_at,
			"extra"      => $user_company->extra,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(int $user_id, int $company_id, array $set):int {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotUser_Company::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` USE INDEX (`PRIMARY`) SET ?u WHERE user_id = ?i AND `company_id` = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $company_id, 1);
	}

	/**
	 * Метод для обновления записей
	 *
	 */
	public static function setList(array $user_id_list, int $company_id, array $set):void {

		$grouped_by_shard = [];

		// группируем по шарду
		foreach ($user_id_list as $user_id) {
			$grouped_by_shard[self::_getDbKey($user_id)][self::_getTableKey($user_id)][] = $user_id;
		}

		// для каждого шарда базы данных
		foreach ($grouped_by_shard as $shard_key => $grouped_by_table_user_id_list) {

			// для каждой таблицы базы данных
			foreach ($grouped_by_table_user_id_list as $table_key => $table_user_id_list) {

				// формируем и осуществляем запрос
				// запрос проверен на EXPLAIN (INDEX=PRIMARY)
				$query = "UPDATE `?p` USE INDEX (`PRIMARY`) SET ?u WHERE user_id IN (?a) AND `company_id` = ?i LIMIT ?i";
				ShardingGateway::database($shard_key)->update($query, $table_key, $set, $table_user_id_list, $company_id, count($table_user_id_list));
			}
		}
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $user_id, int $company_id):Struct_Db_PivotUser_Company {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` USE INDEX (`PRIMARY`) WHERE `user_id`=?i AND `company_id`=?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $company_id, 1);
		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_getStruct($row);
	}

	/**
	 * Метод для получения записей
	 *
	 * @return Struct_Db_PivotUser_Company[]
	 */
	public static function getListForCompany(array $user_id_list, int $company_id):array {

		$grouped_by_shard = [];
		$output           = [];

		// группируем по шарду
		foreach ($user_id_list as $user_id) {
			$grouped_by_shard[self::_getDbKey($user_id)][self::_getTableKey($user_id)][] = $user_id;
		}

		// для каждого шарда базы данных
		foreach ($grouped_by_shard as $shard_key => $grouped_by_table_user_id_list) {

			// для каждой таблицы базы данных
			foreach ($grouped_by_table_user_id_list as $table_key => $table_user_id_list) {

				// формируем и осуществляем запрос
				// запрос проверен на EXPLAIN (INDEX=PRIMARY)
				$query  = "SELECT * FROM `?p` WHERE company_id = ?i AND `user_id` IN (?a) LIMIT ?i";
				$result = ShardingGateway::database($shard_key)->getAll($query, $table_key, $company_id, $table_user_id_list, count($table_user_id_list));

				foreach ($result as $row) {
					$output[] = self::_getStruct($row);
				}
			}
		}
		return $output;
	}

	/**
	 * Метод для получения записей
	 *
	 * @return Struct_Db_PivotUser_Company[]
	 */
	public static function getList(array $user_id_list):array {

		$grouped_by_shard = [];
		$output           = [];

		$limit = 10000;

		// группируем по шарду
		foreach ($user_id_list as $user_id) {
			$grouped_by_shard[self::_getDbKey($user_id)][self::_getTableKey($user_id)][] = $user_id;
		}

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `user_id` IN (?a) LIMIT ?i OFFSET ?i";

		// для каждого шарда базы данных
		foreach ($grouped_by_shard as $shard_key => $grouped_by_table_user_id_list) {

			// для каждой таблицы базы данных
			foreach ($grouped_by_table_user_id_list as $table_key => $table_user_id_list) {

				$offset = 0;
				do {

					$result = ShardingGateway::database($shard_key)->getAll($query, $table_key, $table_user_id_list, $limit, $offset);

					foreach ($result as $row) {
						$output[] = self::_getStruct($row);
					}
					$offset += $limit;
				} while (count($result) == $limit);
			}
		}
		return $output;
	}

	/**
	 * метод для удаления записи
	 *
	 */
	public static function delete(int $user_id, int $company_id):void {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `user_id`=?i AND `company_id`=?i LIMIT ?i";
		ShardingGateway::database($shard_key)->delete($query, $table_name, $user_id, $company_id, 1);
	}

	/**
	 * метод для получения максимального order в таблице по пользователю
	 *
	 */
	public static function getMaxOrder(int $user_id):int {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT MAX(`order`) as max FROM `?p` USE INDEX (`PRIMARY`) WHERE `user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);
		if (!isset($row["max"])) {
			return 0;
		}

		return $row["max"];
	}

	/**
	 * метод для получения списка всех компаний пользователя
	 *
	 * @return Struct_Db_PivotUser_Company[]
	 */
	public static function getCompanyListWithMinOrder(int $user_id, int $min_order, int $limit):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		if ($min_order < 1) {

			// запрос проверен на EXPLAIN (INDEX=user_id_and_order)
			$query = "SELECT * FROM `?p` USE INDEX (`user_id_and_order`) WHERE `user_id`=?i ORDER BY `order` DESC LIMIT ?i";
			$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $limit);
		} else {

			// запрос проверен на EXPLAIN (INDEX=user_id_and_order)
			$query = "SELECT * FROM `?p` USE INDEX (`user_id_and_order`) WHERE `user_id`=?i AND `order` < ?i ORDER BY `order` DESC LIMIT ?i";
			$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $min_order, $limit);
		}

		$output = [];
		foreach ($list as $row) {
			$output[] = self::_getStruct($row);
		}

		return $output;
	}

	/**
	 * метод для получения списка всех компаний пользователя
	 * @return Struct_Db_PivotUser_Company[]
	 */
	public static function getCompanyList(int $user_id):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query  = "SELECT COUNT(company_id) AS `companies_count` FROM `?p` USE INDEX (`PRIMARY`) WHERE user_id=?i LIMIT ?i";
		$result = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` USE INDEX (`PRIMARY`) WHERE `user_id`=?i LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $result["companies_count"]);

		$output = [];
		foreach ($list as $row) {
			$output[] = self::_getStruct($row);
		}

		return $output;
	}

	/**
	 * Метод для получения компаний пользователя по их id
	 *
	 * @param int   $user_id
	 * @param array $company_id_list
	 *
	 * @return Struct_Db_PivotUser_Company[]
	 */
	public static function getCompanyListById(int $user_id, array $company_id_list):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// запрос проверен на EXPLAIN 17.12.2021(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` USE INDEX (`PRIMARY`) WHERE `user_id`=?i AND `company_id` IN (?a) LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $company_id_list, count($company_id_list));

		$output = [];
		foreach ($list as $row) {
			$output[] = self::_getStruct($row);
		}

		return $output;
	}

	/**
	 * Удаляем записи из таблицы для указанной компании.
	 */
	public static function deleteByCompany(int $company_id):void {

		$user_limit_in_table = 1000000;

		//так как номер таблицы зависит от id пользователя, перебираем все таблицы
		for ($table_counter = 1; $table_counter <= 10; $table_counter++) {

			// формируем и осуществляем запрос
			// index не требуется так как запрос не участвует в бизнес логике
			$query = "DELETE FROM `?p` WHERE company_id = ?i LIMIT ?i";
			ShardingGateway::database(self::_getDbKey(1))->delete($query, self::_getTableKey($table_counter), $company_id, $user_limit_in_table);
		}
	}

	/**
	 * Имеется ли одна запись из таблиц для указанной компании.
	 */
	public static function existOneByCompany(int $company_id):bool {

		//так как номер таблицы зависит от id пользователя, перебираем все таблицы
		for ($table_counter = 1; $table_counter <= 10; $table_counter++) {

			// формируем и осуществляем запрос
			// index не требуется так как запрос не участвует в бизнес логике
			$query    = "SELECT * FROM `?p` WHERE company_id = ?i LIMIT ?i";
			$user_row = ShardingGateway::database(self::_getDbKey(1))->getOne($query, self::_getTableKey($table_counter), $company_id, 1);
			if (isset($user_row["company_id"])) {
				return true;
			}
		}

		return false;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получаем структуру из строки из базы
	 *
	 */
	protected static function _getStruct(array $row):Struct_Db_PivotUser_Company {

		return new Struct_Db_PivotUser_Company(
			$row["user_id"],
			$row["company_id"],
			$row["is_has_pin"],
			$row["order"],
			$row["entry_id"],
			$row["created_at"],
			$row["updated_at"],
			fromJson($row["extra"]),
		);
	}

	/**
	 * Получаем таблицу
	 *
	 */
	protected static function _getTableKey(int $user_id):string {

		return self::_TABLE_KEY . "_" . ceil($user_id / 1000000);
	}
}
