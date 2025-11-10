<?php

namespace Compass\Company;

use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;

/**
 * Класс-интерфейс для таблицы company_data.smart_app_list
 */
class Gateway_Db_CompanyData_SmartAppList extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "smart_app_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @param string $smart_app_uniq_name
	 * @param int    $creator_user_id
	 * @param int    $catalog_item_id
	 * @param int    $created_at
	 * @param array  $extra
	 *
	 * @return int
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function insert(string $smart_app_uniq_name, int $creator_user_id, int $catalog_item_id, int $created_at, array $extra):int {

		$insert_row = [
			"catalog_item_id"     => $catalog_item_id,
			"creator_user_id"     => $creator_user_id,
			"created_at"          => $created_at,
			"updated_at"          => 0,
			"smart_app_uniq_name" => $smart_app_uniq_name,
			"extra"               => $extra,
		];

		return ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert_row);
	}

	/**
	 * метод для обновления записи
	 *
	 * @param int   $smart_app_id
	 * @param array $set
	 *
	 * @return int
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function set(int $smart_app_id, array $set):int {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `smart_app_id` = ?i LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $smart_app_id, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @param int $smart_app_id
	 *
	 * @return Struct_Db_CompanyData_SmartAppList
	 * @throws DBShardingNotFoundException
	 * @throws Domain_SmartApp_Exception_SmartAppNotFound
	 * @throws QueryFatalException
	 */
	public static function getOne(int $smart_app_id):Struct_Db_CompanyData_SmartAppList {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `smart_app_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $smart_app_id, 1);

		if (!isset($row["smart_app_id"])) {
			throw new Domain_SmartApp_Exception_SmartAppNotFound("smart app is not found");
		}

		return self::_formatRow($row);
	}

	/**
	 * метод для получения записи по уникальному имени smart app
	 *
	 * @param string $smart_app_uniq_name
	 *
	 * @return Struct_Db_CompanyData_SmartAppList
	 * @throws DBShardingNotFoundException
	 * @throws Domain_SmartApp_Exception_SmartAppNotFound
	 * @throws QueryFatalException
	 */
	public static function getBySmartAppUniqName(string $smart_app_uniq_name):Struct_Db_CompanyData_SmartAppList {

		// запрос проверен на EXPLAIN (INDEX=smart_app_uniq_name)
		$query = "SELECT * FROM `?p` WHERE `smart_app_uniq_name` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $smart_app_uniq_name, 1);

		if (!isset($row["smart_app_id"])) {
			throw new Domain_SmartApp_Exception_SmartAppNotFound("smart app is not found");
		}

		return self::_formatRow($row);
	}

	/**
	 * метод для получения приложений созданных из каталога
	 *
	 * @return array
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function getListCreatedFromCatalog():array {

		// запрос проверен на EXPLAIN (INDEX=catalog_item_id)
		$query = "SELECT * FROM `?p` WHERE `catalog_item_id` != ?i LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, 0, 10000);

		return self::_formatList($list);
	}

	/**
	 * метод для получения приложений созданных НЕ из каталога
	 *
	 * @return array
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function getListCreatedNotFromCatalog():array {

		// запрос проверен на EXPLAIN (INDEX=catalog_item_id)
		$query = "SELECT * FROM `?p` WHERE `catalog_item_id` = ?i LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, 0, 10000);

		return self::_formatList($list);
	}

	/**
	 * метод для получения списка приложений
	 *
	 * @param array $smart_app_id_list
	 *
	 * @return Struct_Db_CompanyData_SmartAppList[]
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function getList(array $smart_app_id_list):array {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `smart_app_id` IN (?a) LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $smart_app_id_list, count($smart_app_id_list));

		return self::_formatList($list);
	}

	/**
	 * метод для получения записи под обновление
	 *
	 * @param int $smart_app_id
	 *
	 * @return Struct_Db_CompanyData_SmartAppList
	 * @throws DBShardingNotFoundException
	 * @throws Domain_SmartApp_Exception_SmartAppNotFound
	 * @throws QueryFatalException
	 */
	public static function getForUpdate(int $smart_app_id):Struct_Db_CompanyData_SmartAppList {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `smart_app_id` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $smart_app_id, 1);

		if (!isset($row["smart_app_id"])) {
			throw new Domain_SmartApp_Exception_SmartAppNotFound("smart app is not found");
		}

		return self::_formatRow($row);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * форматируем список записей из базы
	 *
	 * @return Struct_Db_CompanyData_SmartAppList[]
	 */
	protected static function _formatList(array $list):array {

		return array_map([self::class, "_formatRow"], $list);
	}

	// форматируем одну запись из базы
	protected static function _formatRow(array $row):Struct_Db_CompanyData_SmartAppList {

		if (empty($row)) {
			throw new Domain_SmartApp_Exception_SmartAppNotFound("smart app is not found");
		}
		return Struct_Db_CompanyData_SmartAppList::rowToStruct($row);
	}
}