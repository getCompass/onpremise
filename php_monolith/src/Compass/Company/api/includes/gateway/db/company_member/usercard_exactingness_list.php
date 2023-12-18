<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_member.usercard_exactingness_list
 */
class Gateway_Db_CompanyMember_UsercardExactingnessList extends Gateway_Db_CompanyMember_Main {

	protected const _TABLE_KEY = "usercard_exactingness_list";

	/**
	 * добавляем новую требовательность в базу
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function add(int $user_id, int $creator_user_id, int $type, int $created_at):Struct_Domain_Usercard_Exactingness {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		$insert_row = [
			"user_id"         => $user_id,
			"creator_user_id" => $creator_user_id,
			"type"            => $type,
			"is_deleted"      => 0,
			"created_at"      => $created_at,
			"updated_at"      => 0,
			"data"            => Type_User_Card_Exactingness::initData(),
		];

		$exactingness_id = ShardingGateway::database($shard_key)->insert($table_name, $insert_row);

		$exactingness_id = formatInt($exactingness_id);
		if ($exactingness_id == 0) {
			throw new ParseFatalException("increment in db not working");
		}

		$insert_row["exactingness_id"] = $exactingness_id;

		return self::_rowToObject($insert_row);
	}

	/**
	 * получаем требовательность пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 */
	public static function get(int $user_id, int $exactingness_id):Struct_Domain_Usercard_Exactingness {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `exactingness_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $exactingness_id, 1);

		if (!isset($row["exactingness_id"])) {
			throw new \cs_RowIsEmpty();
		}

		$row["data"] = fromJson($row["data"]);
		return self::_rowToObject($row);
	}

	/**
	 * Получаем список требовательностей по их id
	 *
	 * @return Struct_Domain_Usercard_Exactingness[]
	 *
	 * @throws \parseException
	 */
	public static function getListByIdList(array $exactingness_id_list):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `exactingness_id` IN (?a) ORDER BY `exactingness_id` DESC LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $exactingness_id_list, count($exactingness_id_list));

		$obj_list = [];
		foreach ($list as $k => $row) {

			$row["data"]  = fromJson($row["data"]);
			$obj_list[$k] = self::_rowToObject($row);
		}

		return $obj_list;
	}

	/**
	 * обновляем запись требовательности в базе
	 */
	public static function set(int $user_id, int $exactingness_id, array $set):void {

		$set["updated_at"] = $set["updated_at"] ?? time();

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `exactingness_id` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $exactingness_id, 1);
	}

	/**
	 * обновляем список требовательностей по их id
	 */
	public static function setByIdList(int $user_id, array $exactingness_id_list, array $set):void {

		$set["updated_at"] = $set["updated_at"] ?? time();

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `exactingness_id` IN (?a) LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $exactingness_id_list, count($exactingness_id_list));
	}

	/**
	 * получаем записи за период
	 *
	 * @return Struct_Domain_Usercard_Exactingness[]
	 *
	 * @throws \parseException
	 */
	public static function getAllByPeriod(int $creator_user_id, int $from_date_at, int $to_date_at, int $limit, int $offset):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`get_by_creator_user_and_is_deleted_and_created_at`)
		$query = "SELECT * FROM `?p` WHERE `creator_user_id` = ?i AND `created_at` >= ?i AND `created_at` <= ?i AND `is_deleted` = ?i
		ORDER BY `exactingness_id` DESC LIMIT ?i OFFSET ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $creator_user_id, $from_date_at, $to_date_at, 0, $limit, $offset);

		$obj_list = [];
		foreach ($list as $row) {

			$row["data"] = fromJson($row["data"]);
			$obj_list[]  = self::_rowToObject($row);
		}

		return $obj_list;
	}

	/**
	 * Получаем число записей за время
	 *
	 * @throws ParseFatalException
	 */
	public static function getCountByTime(int $creator_user_id, int $from_date_at):int {

		// запрос проверен на EXPLAIN (INDEX=`get_by_creator_user_and_is_deleted_and_created_at`)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `creator_user_id` = ?i AND `created_at` >= ?i AND `is_deleted` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_getTableName(), $creator_user_id, $from_date_at, 0, 1);

		return $row["count"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * создаем сткруктуру из строки бд
	 *
	 * @throws \parseException
	 */
	protected static function _rowToObject(array $row):Struct_Domain_Usercard_Exactingness {

		foreach ($row as $field => $_) {
			if (!property_exists(Struct_Domain_Usercard_Exactingness::class, $field)) {

				throw new ParseFatalException("send unknown field = '{$field}'");
			}
		}

		return new Struct_Domain_Usercard_Exactingness(
			$row["exactingness_id"],
			$row["type"],
			$row["user_id"],
			$row["creator_user_id"],
			$row["is_deleted"],
			$row["created_at"],
			$row["updated_at"],
			Type_User_Card_Exactingness::actualData($row["data"]),
		);
	}

	/**
	 * функция возвращает название таблицы
	 */
	protected static function _getTableName():string {

		return self::_TABLE_KEY;
	}
}