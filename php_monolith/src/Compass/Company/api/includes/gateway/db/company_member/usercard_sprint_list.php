<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_member.usercard_sprint_list
 */
class Gateway_Db_CompanyMember_UsercardSprintList extends Gateway_Db_CompanyMember_Main {

	protected const _TABLE_KEY = "usercard_sprint_list";

	/**
	 * Добавляем новый спринт пользователя в базу
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function add(int $user_id, int $creator_user_id, int $is_success, int $started_at, int $end_at, string $header_text, string $description_text, array $data):Struct_Domain_Usercard_Sprint {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		$insert_row = [
			"user_id"          => $user_id,
			"is_success"       => $is_success,
			"is_deleted"       => 0,
			"creator_user_id"  => $creator_user_id,
			"started_at"       => $started_at,
			"end_at"           => $end_at,
			"created_at"       => time(),
			"updated_at"       => 0,
			"header_text"      => $header_text,
			"description_text" => $description_text,
			"data"             => $data,
		];

		$sprint_id = ShardingGateway::database($shard_key)->insert($table_name, $insert_row);

		$sprint_id = formatInt($sprint_id);
		if ($sprint_id == 0) {
			throw new ParseFatalException("increment in db not working");
		}

		$insert_row["sprint_id"] = $sprint_id;

		return self::_rowToObject($insert_row);
	}

	/**
	 * Обновляем запись в базе
	 */
	public static function set(int $user_id, int $sprint_id, array $set):void {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `sprint_id` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $sprint_id, 1);
	}

	/**
	 * Достаем конкретную запись пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(int $user_id, int $sprint_id):Struct_Domain_Usercard_Sprint {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `sprint_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $sprint_id, 1);

		if (!isset($row["sprint_id"])) {
			throw new \cs_RowIsEmpty();
		}

		// переводим в объект
		$row["data"] = fromJson($row["data"]);
		return self::_rowToObject($row);
	}

	/**
	 * возвращает список спринтов пользователя
	 *
	 * @return Struct_Domain_Usercard_Sprint[]
	 */
	public static function getList(int $user_id, int $offset, int $limit):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=get_by_user_id_and_is_deleted)
		$query = "SELECT * FROM `?p` FORCE INDEX(`get_by_user_id_and_is_deleted`) WHERE `user_id` = ?i AND `is_deleted` = ?i 
				ORDER BY `end_at` DESC, `sprint_id` DESC LIMIT ?i OFFSET ?i";

		// осуществляем запрос
		$list = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, 0, $limit, $offset);

		// разджейсониваем поля
		$obj_list = [];
		foreach ($list as $row) {

			$row["data"] = fromJson($row["data"]);
			$obj_list[]  = self::_rowToObject($row);
		}

		return $obj_list;
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * создаем сткруктуру из строки бд
	 */
	protected static function _rowToObject(array $row):Struct_Domain_Usercard_Sprint {

		return new Struct_Domain_Usercard_Sprint(
			$row["sprint_id"],
			$row["user_id"],
			$row["is_success"],
			$row["is_deleted"],
			$row["creator_user_id"],
			$row["started_at"],
			$row["end_at"],
			$row["created_at"],
			$row["updated_at"],
			$row["header_text"],
			$row["description_text"],
			Type_User_Card_Sprint::actualData($row["data"]),
		);
	}

	/**
	 * функция возвращает название таблицы
	 */
	protected static function _getTableName():string {

		return self::_TABLE_KEY;
	}
}