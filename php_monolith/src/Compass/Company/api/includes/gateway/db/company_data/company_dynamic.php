<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Интерфейс для работы с таблицу company_data.company_dynamic
 */
class Gateway_Db_CompanyData_CompanyDynamic extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "company_dynamic";

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_CompanyData_CompanyDynamic $dynamic):string {

		$insert = [
			"key"        => $dynamic->key,
			"value"      => $dynamic->value,
			"created_at" => $dynamic->created_at,
			"updated_at" => $dynamic->updated_at,
		];

		// осуществляем запрос
		return ShardingGateway::database(self::_getDbKey())->insert(self::_TABLE_KEY, $insert);
	}

	/**
	 * Получение значения из БД
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(string $key):Struct_Db_CompanyData_CompanyDynamic {

		$query = "SELECT * FROM `?p` WHERE `key` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $key, 1);

		if (!isset($row["key"])) {
			throw new \cs_RowIsEmpty();
		}
		return self::_rowToObject($row);
	}

	/**
	 * Получение значения из БД с блокировкой
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getForUpdate(string $key):Struct_Db_CompanyData_CompanyDynamic {

		$query = "SELECT * FROM `?p` WHERE `key` = ?s LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $key, 1);

		if (!isset($row["key"])) {
			throw new \cs_RowIsEmpty();
		}
		return self::_rowToObject($row);
	}

	/**
	 * Получение массив значений из БД
	 */
	public static function getList(array $allow_key_list):array {

		$query = "SELECT * FROM `?p` WHERE `key` IN (?a) LIMIT ?i";
		$rows  = ShardingGateway::database(self::_getDbKey())->getAll($query, self::_TABLE_KEY, $allow_key_list, 1000);

		$list = [];
		foreach ($rows as $row) {
			$list[] = self::_rowToObject($row);
		}

		return $list;
	}

	/**
	 * Установить атрибут
	 *
	 * @throws cs_RowNotUpdated
	 * @throws \parseException
	 */
	public static function set(string $key, array $set):int {

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_CompanyData_CompanyDynamic::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		$query        = "UPDATE `?p` SET ?u WHERE `key` = ?s LIMIT ?i";
		$update_count = ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $key, 1);

		if ($update_count === 0) {
			throw new cs_RowNotUpdated();
		}

		return $update_count;
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	/**
	 * Создаем структуру из строки бд
	 */
	protected static function _rowToObject(array $row):Struct_Db_CompanyData_CompanyDynamic {

		return new Struct_Db_CompanyData_CompanyDynamic(
			$row["key"],
			$row["value"],
			$row["created_at"],
			$row["updated_at"],
		);
	}
}
