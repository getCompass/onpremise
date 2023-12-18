<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_data.userbot_list
 */
class Gateway_Db_CompanyData_UserbotList extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "userbot_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 */
	public static function insert(string $userbot_id, int $user_id, int $status, int $created_at, array $extra):void {

		$insert_row = [
			"userbot_id"   => $userbot_id,
			"status_alias" => $status,
			"user_id"      => $user_id,
			"created_at"   => $created_at,
			"updated_at"   => 0,
			"extra"        => $extra,
		];

		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert_row);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(string $userbot_id, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_CloudCompany_Userbot::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `userbot_id` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $userbot_id, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 */
	public static function getOne(string $userbot_id):Struct_Db_CloudCompany_Userbot {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `userbot_id` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $userbot_id, 1);

		if (!isset($row["userbot_id"])) {
			throw new Domain_Userbot_Exception_UserbotNotFound("user is not found");
		}

		return self::_rowToObject($row);
	}

	/**
	 * метод для получения списка ботов
	 *
	 * @return Struct_Db_CloudCompany_Userbot[]
	 */
	public static function getList(array $userbot_id_list):array {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `userbot_id` IN (?a) LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $userbot_id_list, count($userbot_id_list));

		$obj_list = [];
		foreach ($list as $index => $row) {
			$obj_list[] = self::_rowToObject($row);
		}

		return $obj_list;
	}

	/**
	 * метод для получения всех записей
	 *
	 * @return Struct_Db_CloudCompany_Userbot[]
	 */
	public static function getAll():array {

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE TRUE LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, 9999);

		$obj_list = [];
		foreach ($list as $index => $row) {
			$obj_list[] = self::_rowToObject($row);
		}

		return $obj_list;
	}

	/**
	 * метод для получения записи под обновление
	 *
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 */
	public static function getForUpdate(string $userbot_id):Struct_Db_CloudCompany_Userbot {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `userbot_id` = ?s LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $userbot_id, 1);

		if (!isset($row["userbot_id"])) {
			throw new Domain_Userbot_Exception_UserbotNotFound("user is not found");
		}

		return self::_rowToObject($row);
	}

	/**
	 * получаем записи по списку user_id
	 *
	 * @return Struct_Db_CloudCompany_Userbot[]
	 */
	public static function getByUserIdList(array $user_id_list):array {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`get_by_user_id`)
		$query = "SELECT * FROM `?p` WHERE `user_id` IN (?a) LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $user_id_list, count($user_id_list));

		$obj_list = [];
		foreach ($list as $index => $row) {
			$obj_list[] = self::_rowToObject($row);
		}

		return $obj_list;
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	/**
	 * Создаем структуру из строки бд
	 */
	protected static function _rowToObject(array $row):Struct_Db_CloudCompany_Userbot {

		return new Struct_Db_CloudCompany_Userbot(
			$row["userbot_id"],
			$row["status_alias"],
			$row["user_id"],
			$row["created_at"],
			$row["updated_at"],
			fromJson($row["extra"])
		);
	}
}