<?php

namespace Compass\Company;

/**
 * Класс-интерфейс для таблицы company_data.entry_list
 */
class Gateway_Db_CompanyData_EntryList extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "entry_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для вставки записи
	 *
	 * @throws \queryException
	 * @mixed
	 */
	public static function insert(int $entry_type, int $user_id):int {

		$insert = [
			"entry_type" => $entry_type,
			"user_id"    => $user_id,
			"created_at" => time(),
		];

		// осуществляем запрос
		return ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert);
	}

	/**
	 * метод для получшения записи
	 *
	 * @throws \cs_RowIsEmpty
	 * @mixed
	 */
	public static function getOne(int $entry_id):Struct_Db_CompanyData_Entry {

		// осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `entry_id`=?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $entry_id, 1);
		if (!isset($row["entry_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return new Struct_Db_CompanyData_Entry(
			$row["entry_id"],
			$row["entry_type"],
			$row["user_id"],
			$row["created_at"],
		);
	}

	/**
	 * метод для получшения записи
	 *
	 * @return Struct_Db_CompanyData_Entry[]
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 *
	 */
	public static function getList(array $entry_id_list):array {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `entry_id` IN (?a) LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $entry_id_list, count($entry_id_list));

		$obj_list = [];
		foreach ($list as $row) {

			$obj_list[] = new Struct_Db_CompanyData_Entry(
				$row["entry_id"],
				$row["entry_type"],
				$row["user_id"],
				$row["created_at"],
			);
		}

		return $obj_list;
	}

	/**
	 * метод для получения последней записи entry
	 *
	 * @throws \cs_RowIsEmpty
	 * @mixed
	 */
	public static function getEntryLast(int $user_id):Struct_Db_CompanyData_Entry {

		// осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id`=?i ORDER BY entry_id DESC LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $user_id, 1);
		if (!isset($row["entry_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return new Struct_Db_CompanyData_Entry(
			$row["entry_id"],
			$row["entry_type"],
			$row["user_id"],
			$row["created_at"],
		);
	}
}