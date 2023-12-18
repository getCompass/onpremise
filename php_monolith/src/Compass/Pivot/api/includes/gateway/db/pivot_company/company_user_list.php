<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_company.company_user_list_{1}
 */
class Gateway_Db_PivotCompany_CompanyUserList extends Gateway_Db_PivotCompany_Main {

	protected const _TABLE_KEY = "company_user_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException|cs_CompanyIncorrectCompanyId
	 */
	public static function insert(Struct_Db_PivotCompany_CompanyUser $company_user):string {

		$shard_key  = self::_getDbKey($company_user->company_id);
		$table_name = self::_getTableKey($company_user->company_id);

		$insert = [
			"company_id" => $company_user->company_id,
			"user_id"    => $company_user->user_id,
			"created_at" => $company_user->created_at,
			"updated_at" => $company_user->updated_at,
			"extra"      => $company_user->extra,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function getOne(int $company_id, int $user_id):Struct_Db_PivotCompany_CompanyUser {

		$shard_key  = self::_getDbKey($company_id);
		$table_name = self::_getTableKey($company_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `company_id`=?i AND `user_id`=?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $company_id, $user_id, 1);
		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_getStruct($row);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function set(int $company_id, int $user_id, array $set):int {

		$shard_key  = self::_getDbKey($company_id);
		$table_name = self::_getTableKey($company_id);

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotCompany_CompanyUser::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE company_id = ?i AND user_id = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $company_id, $user_id, 1);
	}

	/**
	 * получаем список пользователей компании
	 *
	 */
	public static function getFullUserIdList(int $company_id):array {

		$shard_key  = self::_getDbKey($company_id);
		$table_name = self::_getTableKey($company_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query  = "SELECT COUNT(*) AS `count` FROM `?p` WHERE company_id=?i LIMIT ?i";
		$result = ShardingGateway::database($shard_key)->getOne($query, $table_name, $company_id, 1);

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE company_id=?i LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $company_id, $result["count"]);

		$output = [];
		foreach ($list as $row) {
			$output[] = (int) $row["user_id"];
		}

		return $output;
	}

	/**
	 * Проверяем, состоят ли пользователи в компании, исключаем лишних
	 *
	 * @return Struct_Db_PivotCompany_CompanyUser[]
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	public static function getByUserIdList(int $company_id, array $user_id_list, bool $is_assoc = false):array {

		$shard_key  = self::_getDbKey($company_id);
		$table_name = self::_getTableKey($company_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `company_id`=?i AND `user_id` IN (?a) LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $company_id, $user_id_list, count($user_id_list));

		$output = [];
		foreach ($list as $row) {

			if ($is_assoc) {
				$output[$row["user_id"]] = self::_getStruct($row);
			} else {
				$output[] = self::_getStruct($row);
			}
		}

		return $output;
	}

	/**
	 * Удаляем записи из таблицы для указанной компании.
	 * Используется для очистки компаний.
	 *
	 */
	public static function deleteByCompany(int $company_id):void {

		$shard_key  = self::_getDbKey($company_id);
		$table_name = self::_getTableKey($company_id);

		// формируем и осуществляем запрос
		$query = "DELETE FROM `?p` WHERE company_id = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->delete($query, $table_name, $company_id, 900000);
	}

	/**
	 * метод для удаления записи
	 *
	 */
	public static function delete(int $user_id, int $company_id):void {

		$shard_key  = self::_getDbKey($company_id);
		$table_name = self::_getTableKey($company_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `company_id`=?i AND `user_id`=?i LIMIT ?i";
		ShardingGateway::database($shard_key)->delete($query, $table_name, $company_id, $user_id, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получаем структуру из строки из базы
	 *
	 */
	protected static function _getStruct(array $company_user_row):Struct_Db_PivotCompany_CompanyUser {

		return new Struct_Db_PivotCompany_CompanyUser(
			$company_user_row["company_id"],
			$company_user_row["user_id"],
			$company_user_row["created_at"],
			$company_user_row["updated_at"],
			fromJson($company_user_row["extra"])
		);
	}

	/**
	 *
	 * Получает таблицу
	 *
	 * @throws cs_CompanyIncorrectCompanyId
	 */
	protected static function _getTableKey(int $company_id):string {

		$shard = ceil($company_id / 1000000);

		$table_shard = self::_TABLE_KEY . "_$shard";
		self::_checkExistShard($table_shard, $company_id);

		return $table_shard;
	}
}
