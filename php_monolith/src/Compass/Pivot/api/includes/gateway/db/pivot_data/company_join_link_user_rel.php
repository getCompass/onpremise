<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_data.company_join_link_user_rel
 */
class Gateway_Db_PivotData_CompanyJoinLinkUserRel extends Gateway_Db_PivotData_Main {

	protected const _TABLE_KEY = "company_join_link_user_rel";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 * @throws cs_RowDuplication
	 */
	public static function insert(string $join_link_uniq, int $user_id, int $company_id, int $entry_id, int $status):void {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$insert = [
			"join_link_uniq" => $join_link_uniq,
			"user_id"        => $user_id,
			"company_id"     => $company_id,
			"entry_id"       => $entry_id,
			"status"         => $status,
			"created_at"     => time(),
			"updated_at"     => 0,
		];

		// осуществляем запрос
		try {

			ShardingGateway::database($shard_key)->insert($table_name, $insert, false);
		} catch (\PDOException $e) {

			if ($e->getCode() == 23000) {
				throw new cs_RowDuplication();
			}
		}
	}

	/**
	 * метод для обновления записи (по entry_id)
	 *
	 * @throws ParseFatalException
	 */
	public static function set(int $entry_id, int $user_id, int $company_id, array $set):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		foreach ($set as $field_name => $field) {

			if (!property_exists(Struct_Db_PivotData_CompanyJoinLinkUserRel::class, $field_name)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=entry_user_company_id)
		$query = "UPDATE `?p` SET ?u WHERE entry_id = ?s AND user_id = ?i AND company_id = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $entry_id, $user_id, $company_id, 1);
	}

	/**
	 * метод для обновления записи (по join_link_uniq)
	 *
	 * @throws ParseFatalException
	 */
	public static function setByJoinLink(string $join_link_uniq, int $user_id, int $company_id, array $set):int {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		foreach ($set as $field_name => $field) {

			if (!property_exists(Struct_Db_PivotData_CompanyJoinLinkUserRel::class, $field_name)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE join_link_uniq = ?s AND user_id = ?i AND company_id = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $join_link_uniq, $user_id, $company_id, 1);
	}

	/**
	 * достаем запись из таблицы
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getByInviteLink(string $join_link_uniq, int $user_id, int $company_id):Struct_Db_PivotData_CompanyJoinLinkUserRel {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE join_link_uniq = ?s AND user_id = ?i AND company_id = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $join_link_uniq, $user_id, $company_id, 1);

		if (!isset($row["join_link_uniq"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * получаем записи для компании по статусу
	 */
	public static function getByCompanyIdAndStatus(int $company_id, int $status):array {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=`company_id.status`)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE company_id = ?i AND status = ?i LIMIT ?i";
		$count = ShardingGateway::database(self::_DB_KEY)->getOne($query, $table_name, $company_id, $status, 1)["count"] ?? 0;

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`company_id.status`)
		$query = "SELECT * FROM `?p` WHERE company_id = ?i AND status = ?i LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $company_id, $status, $count);

		$obj_list = [];
		foreach ($list as $row) {
			$obj_list[] = self::_rowToStruct($row);
		}

		return $obj_list;
	}

	/**
	 * получаем записи для компании по статусу
	 */
	public static function getByUserId(int $user_id):Struct_Db_PivotData_CompanyJoinLinkUserRel {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`user_id.created_at`)
		$query = "SELECT * FROM `?p` WHERE user_id = ?i ORDER BY created_at ASC LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);

		if (!isset($row["join_link_uniq"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * Получаем запись по entry_id, user_id, company_id
	 *
	 * @return Struct_Db_PivotData_CompanyJoinLinkUserRel
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 */
	public static function getByEntryUserCompany(int $entry_id, int $user_id, int $company_id):Struct_Db_PivotData_CompanyJoinLinkUserRel {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`entry_user_company`)
		$query = "SELECT * FROM `?p` WHERE entry_id = ?i AND user_id = ?i AND company_id = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $entry_id, $user_id, $company_id, 1);

		if (!isset($row["join_link_uniq"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получаем таблицу
	 */
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}

	/**
	 * преобразуем строку записи базы в объект
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotData_CompanyJoinLinkUserRel {

		return new Struct_Db_PivotData_CompanyJoinLinkUserRel(
			$row["join_link_uniq"],
			$row["user_id"],
			$row["company_id"],
			$row["entry_id"],
			$row["status"],
			$row["created_at"],
			$row["updated_at"],
		);
	}
}