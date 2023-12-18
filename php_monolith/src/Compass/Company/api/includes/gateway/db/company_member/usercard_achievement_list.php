<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_member.usercard_achievement_list
 */
class Gateway_Db_CompanyMember_UsercardAchievementList extends Gateway_Db_CompanyMember_Main {

	public const _TABLE_KEY = "usercard_achievement_list";

	/**
	 * Добавляем новое достижение пользователя в базу
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function add(int $user_id, int $creator_user_id, int $type, string $header_text, string $description_text, array $data):Struct_Domain_Usercard_Achievement {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		$insert_row = [
			"user_id"          => $user_id,
			"creator_user_id"  => $creator_user_id,
			"type"             => $type,
			"is_deleted"       => 0,
			"created_at"       => time(),
			"updated_at"       => 0,
			"header_text"      => $header_text,
			"description_text" => $description_text,
			"data"             => $data,
		];

		$achievement_id = ShardingGateway::database($shard_key)->insert($table_name, $insert_row);

		$achievement_id = formatInt($achievement_id);
		if ($achievement_id == 0) {
			throw new ParseFatalException("increment in db not working");
		}

		$insert_row["achievement_id"] = $achievement_id;

		return self::_rowToObject($insert_row);
	}

	/**
	 * Обновляем запись достижения в базе
	 */
	public static function set(int $user_id, int $achievement_id, array $set):void {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `achievement_id` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $achievement_id, 1);
	}

	/**
	 * обновляем список достижений по их id
	 */
	public static function setByIdList(int $user_id, array $achievement_id_list, array $set):void {

		$set["updated_at"] = $set["updated_at"] ?? time();

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `achievement_id` IN (?a) LIMIT ?i";
		ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $achievement_id_list, count($achievement_id_list));
	}

	/**
	 * Достаем конкретное достижение пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(int $user_id, int $achievement_id):Struct_Domain_Usercard_Achievement {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `achievement_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, $achievement_id, 1);

		if (!isset($row["achievement_id"])) {
			throw new \cs_RowIsEmpty();
		}

		// переводим в объект
		$row["data"] = fromJson($row["data"]);
		return self::_rowToObject($row);
	}

	/**
	 * метод для получения последних записей
	 *
	 * @return Struct_Domain_Usercard_Achievement[]
	 */
	public static function getLastAchievementList(int $user_id, int $limit):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`get_by_user_id_is_deleted`)
		$query        = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `is_deleted` = ?i ORDER BY `achievement_id` DESC LIMIT ?i";
		$loyalty_list = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, 0, $limit);

		$obj_list = [];
		foreach ($loyalty_list as $row) {

			$row["data"] = fromJson($row["data"]);
			$obj_list[]  = self::_rowToObject($row);
		}
		return $obj_list;
	}

	/**
	 * метод для получения записей после achievement_id
	 *
	 * @return Struct_Domain_Usercard_Achievement[]
	 */
	public static function getAchievementListAfterId(int $user_id, int $last_achievement_id, int $limit):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (FORCE INDEX=`get_by_user_id_is_deleted`)
		$query = "SELECT * FROM `?p` FORCE INDEX(`get_by_user_id_is_deleted`) 
				WHERE `user_id` = ?i AND `achievement_id` < ?i AND `is_deleted` = ?i ORDER BY `achievement_id` DESC LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $last_achievement_id, 0, $limit);

		$obj_list = [];
		foreach ($list as $row) {

			$row["data"] = fromJson($row["data"]);
			$obj_list[]  = self::_rowToObject($row);
		}
		return $obj_list;
	}

	/**
	 * метод для получения достижений по их id
	 *
	 * @return Struct_Domain_Usercard_Achievement[]
	 */
	public static function getListByIdList(array $achievement_id_list):array {

		// получаем ключ базы данных
		$shard_key = self::_getDbKey();

		// получаем название таблицы
		$table_name = self::_getTableName();

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `achievement_id` IN (?a) AND `is_deleted` = ?i LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $achievement_id_list, 0, count($achievement_id_list));

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
	protected static function _rowToObject(array $row):Struct_Domain_Usercard_Achievement {

		return new Struct_Domain_Usercard_Achievement(
			$row["achievement_id"],
			$row["user_id"],
			$row["creator_user_id"],
			$row["type"],
			$row["is_deleted"],
			$row["created_at"],
			$row["updated_at"],
			$row["header_text"],
			$row["description_text"],
			Type_User_Card_Achievement::actualData($row["data"]),
		);
	}

	/**
	 * функция возвращает название таблицы
	 */
	protected static function _getTableName():string {

		return self::_TABLE_KEY;
	}
}