<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс-интерфейс для таблицы pivot_company.company_user_list_{1}
 */
class Gateway_Db_PivotCompany_CompanyUserList extends Gateway_Db_PivotCompany_Main {

	protected const _TABLE_KEY = "company_user_list";

	/**
	 * метод для получения записи
	 *
	 * @param int $company_id
	 * @param int $user_id
	 *
	 * @return Struct_Db_PivotCompany_CompanyUser
	 * @throws ParseFatalException
	 * @throws RowNotFoundException
	 */
	public static function getOne(int $company_id, int $user_id):Struct_Db_PivotCompany_CompanyUser {

		$shard_key  = self::_getDbKey($company_id);
		$table_name = self::_getTableKey($company_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `company_id`=?i AND `user_id`=?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $company_id, $user_id, 1);
		if (!isset($row["user_id"])) {
			throw new RowNotFoundException("row not found");
		}

		return self::_getStruct($row);
	}

	/**
	 * метод для получения нескольких записей
	 *
	 * @param int   $company_id
	 * @param array $user_id_list
	 *
	 * @return Struct_Db_PivotCompany_CompanyUser[]
	 * @throws ParseFatalException
	 */
	public static function getList(int $company_id, array $user_id_list):array {

		$shard_key  = self::_getDbKey($company_id);
		$table_name = self::_getTableKey($company_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `company_id`=?i AND `user_id` IN (?a) LIMIT ?i";
		$result   = ShardingGateway::database($shard_key)->getAll($query, $table_name, $company_id, $user_id_list, count($user_id_list));

		return array_map(fn (array $row) => self::_getStruct($row),$result);
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
	 * @param int $company_id
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	protected static function _getTableKey(int $company_id):string {

		$shard = ceil($company_id / 1000000);

		$table_shard = self::_TABLE_KEY . "_$shard";
		self::_checkExistShard($table_shard, $company_id);

		return $table_shard;
	}
}
