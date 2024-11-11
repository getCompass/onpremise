<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_user_{10m}.company_lobby_list_{1}
 */
class Gateway_Db_PivotUser_CompanyLobbyList extends Gateway_Db_PivotUser_Main {

	protected const _TABLE_KEY = "company_lobby_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания/обновления записи
	 *
	 */
	public static function insertOrUpdate(Struct_Db_PivotUser_CompanyLobby $user_company):void {

		$shard_key  = self::_getDbKey($user_company->user_id);
		$table_name = self::_getTableKey($user_company->user_id);

		$insert = [
			"user_id"    => $user_company->user_id,
			"company_id" => $user_company->company_id,
			"order"      => $user_company->order,
			"status"     => $user_company->status,
			"entry_id"   => $user_company->entry_id,
			"created_at" => $user_company->created_at,
			"updated_at" => $user_company->updated_at,
			"extra"      => $user_company->extra,
		];

		// осуществляем запрос
		ShardingGateway::database($shard_key)->insertOrUpdate($table_name, $insert);
	}

	/**
	 * добавляем инсерты для нескольких пользователей разом
	 *
	 */
	public static function insertArray(array $user_lobby_list):void {

		$grouped_by_shard = [];

		// группируем по шарду
		foreach ($user_lobby_list as $user_id => $user_lobby) {
			$grouped_by_shard[self::_getDbKey($user_id)][self::_getTableKey($user_id)][] = (array) $user_lobby;
		}

		// для каждого шарда базы данных
		foreach ($grouped_by_shard as $shard_key => $table_key_list) {

			// для каждой таблицы базы данных
			foreach ($table_key_list as $table_key => $table_user_lobby_list) {
				ShardingGateway::database($shard_key)->insertArray($table_key, $table_user_lobby_list);
			}
		}
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

			if (!property_exists(Struct_Db_PivotUser_CompanyLobby::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` USE INDEX (`PRIMARY`) SET ?u WHERE user_id = ?i AND `company_id` = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $company_id, 1);
	}

	/**
	 * метод для удаления записи
	 *
	 */
	public static function delete(int $user_id, array $company_id_list):void {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `user_id`=?i AND `company_id` IN (?a) LIMIT ?i";
		ShardingGateway::database($shard_key)->delete($query, $table_name, $user_id, $company_id_list, count($company_id_list));
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $user_id, int $company_id):Struct_Db_PivotUser_CompanyLobby {

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
	 * @return Struct_Db_PivotUser_CompanyLobby[]
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

	/**
	 * Метод для получения компаний пользователя по их id
	 *
	 * @return array
	 */
	public static function getCompanyListById(int $user_id, array $company_id_list):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` USE INDEX (`PRIMARY`) WHERE `user_id`=?i AND `company_id` in (?a) LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $company_id_list, count($company_id_list));

		$output = [];
		foreach ($list as $row) {
			$output[] = self::_getStruct($row);
		}

		return $output;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получаем структуру из строки из базы
	 *
	 */
	protected static function _getStruct(array $row):Struct_Db_PivotUser_CompanyLobby {

		return new Struct_Db_PivotUser_CompanyLobby(
			$row["user_id"],
			$row["company_id"],
			$row["order"],
			$row["status"],
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
