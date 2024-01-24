<?php

namespace Compass\Pivot;

/**
 * класс-интерфейс для таблицы pivot_user_{10m}.company_inbox_{ceil}
 */
class Gateway_Db_PivotUser_CompanyInbox extends Gateway_Db_PivotUser_Main {

	protected const _TABLE_KEY = "company_inbox";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для обновления записи
	 *
	 */
	public static function set(int $user_id, int $company_id, array $set):void {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `company_id` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $company_id, 1);
	}

	/**
	 * метод для создания или обновления записи
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotUser_CompanyInbox $user_company_dynamic):string {

		$shard_key  = self::_getDbKey($user_company_dynamic->user_id);
		$table_name = self::_getTableKey($user_company_dynamic->user_id);

		$insert = [
			"user_id"                     => $user_company_dynamic->user_id,
			"company_id"                  => $user_company_dynamic->company_id,
			"messages_unread_count_alias" => $user_company_dynamic->messages_unread_count,
			"inbox_unread_count"          => $user_company_dynamic->inbox_unread_count,
			"created_at"                  => $user_company_dynamic->created_at,
			"updated_at"                  => $user_company_dynamic->updated_at,
		];

		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для получения записи пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $user_id, int $company_id):Struct_Db_PivotUser_CompanyInbox {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `company_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $company_id, 1);

		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * метод для получения и блокировки записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getForUpdate(int $user_id, int $company_id):Struct_Db_PivotUser_CompanyInbox {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id`=?i AND `company_id`=?i LIMIT ?i FOR UPDATE";

		$row = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $company_id, 1);

		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToStruct($row);
	}

	/**
	 * метод для получения записи по id пользователя и компаний
	 *
	 */
	public static function getByCompanyList(int $user_id, array $company_id_list):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		$result = [];

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query    = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `company_id` IN (?a) LIMIT ?i";
		$row_list = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $company_id_list, count($company_id_list));

		foreach ($row_list as $row) {
			$result[$row["company_id"]] = self::_rowToStruct($row);
		}

		return $result;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получает таблицу
	 *
	 */
	protected static function _getTableKey(int $user_id):string {

		return self::_TABLE_KEY . "_" . ceil($user_id / 1000000);
	}

	/**
	 * преобразуем строку записи базы в объект
	 *
	 */
	protected static function _rowToStruct(array $row):Struct_Db_PivotUser_CompanyInbox {

		return new Struct_Db_PivotUser_CompanyInbox(
			$row["user_id"],
			$row["company_id"],
			$row["messages_unread_count_alias"],
			$row["inbox_unread_count"],
			$row["created_at"],
			$row["updated_at"],
		);
	}
}