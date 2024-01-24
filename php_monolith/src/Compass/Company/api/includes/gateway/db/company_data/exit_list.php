<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс-интерфейс для таблицы company_data.exit_list
 */
class Gateway_Db_CompanyData_ExitList extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "exit_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 */
	public static function insert(int $exit_id, int $status, int $user_id, array $extra):Struct_Db_CompanyData_ExitList {

		$insert_row = [
			"exit_task_id" => $exit_id,
			"user_id"      => $user_id,
			"status"       => $status,
			"step"         => Domain_User_Entity_TaskExit::STATUS_IN_PROGRESS,
			"created_at"   => time(),
			"updated_at"   => time(),
			"extra"        => $extra,
		];

		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert_row);

		// отдаем структуру заявки
		return self::_rowToObject($insert_row);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(int $exit_task_id, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_CompanyData_ExitList::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "UPDATE `?p` SET ?u WHERE exit_task_id = ?i LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $exit_task_id, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $exit_task_id):Struct_Db_CompanyData_ExitList {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `exit_task_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $exit_task_id, 1);

		if (!isset($row["exit_task_id"])) {
			throw new \cs_RowIsEmpty();
		}

		$row["extra"] = fromJson($row["extra"]);
		return self::_rowToObject($row);
	}

	/**
	 * метод для получения записи под обновление
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getForUpdate(int $task_id):Struct_Db_CompanyData_ExitList {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `exit_task_id` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $task_id, 1);

		if (!isset($row["exit_task_id"])) {
			throw new \cs_RowIsEmpty();
		}

		$row["extra"] = fromJson($row["extra"]);
		return self::_rowToObject($row);
	}

	/**
	 * получаем записи по статусу задачи
	 *
	 * @return Struct_Db_CompanyData_ExitList[]
	 */
	public static function getAllByStatus(int $status):array {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=`status`)
		$query = "SELECT * FROM `?p` WHERE `status` = ?i LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $status, 1000);

		$obj_list = [];

		foreach ($list as $row) {

			$row["extra"] = fromJson($row["extra"]);
			$obj_list[]   = self::_rowToObject($row);
		}

		return $obj_list;
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	/**
	 * Создаем структуру из строки бд
	 */
	protected static function _rowToObject(array $row):Struct_Db_CompanyData_ExitList {

		return new Struct_Db_CompanyData_ExitList(
			$row["exit_task_id"],
			$row["user_id"],
			$row["status"],
			$row["step"],
			$row["created_at"],
			$row["updated_at"],
			$row["extra"]
		);
	}
}