<?php

namespace Compass\Company;

use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;

/**
 * Класс-интерфейс для таблицы company_data.smart_app_user_rel
 */
class Gateway_Db_CompanyData_SmartAppUserRel extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "smart_app_user_rel";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @param int   $smart_app_id
	 * @param int   $user_id
	 * @param int   $status
	 * @param int   $deleted_at
	 * @param int   $created_at
	 * @param array $extra
	 *
	 * @return void
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function insert(int $smart_app_id, int $user_id, int $status, int $deleted_at, int $created_at, array $extra):void {

		$insert_row = [
			"smart_app_id" => $smart_app_id,
			"user_id"      => $user_id,
			"status"       => $status,
			"deleted_at"   => $deleted_at,
			"created_at"   => $created_at,
			"updated_at"   => 0,
			"extra"        => $extra,
		];

		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert_row);
	}

	/**
	 * метод для обновления записи
	 *
	 * @param int   $smart_app_id
	 * @param int   $user_id
	 * @param array $set
	 *
	 * @return int
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function set(int $smart_app_id, int $user_id, array $set):int {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `smart_app_id` = ?i AND `user_id` = ?i LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $smart_app_id, $user_id, 1);
	}

	/**
	 * метод для получения записи
	 *
	 * @param int $smart_app_id
	 * @param int $user_id
	 *
	 * @return Struct_Db_CompanyData_SmartAppUserRel
	 * @throws DBShardingNotFoundException
	 * @throws Domain_SmartApp_Exception_SmartAppNotFound
	 * @throws QueryFatalException
	 */
	public static function getOne(int $smart_app_id, int $user_id):Struct_Db_CompanyData_SmartAppUserRel {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `smart_app_id` = ?i AND `user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $smart_app_id, $user_id, 1);

		if (!isset($row["smart_app_id"])) {
			throw new Domain_SmartApp_Exception_SmartAppNotFound("smart app is not found");
		}

		return self::_formatRow($row);
	}

	/**
	 * метод для получения записи
	 *
	 * @param int $smart_app_id
	 *
	 * @return array
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function getListBySmartAppId(int $smart_app_id):array {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `smart_app_id` = ?i LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $smart_app_id, 10000);

		return self::_formatList($list);
	}

	/**
	 * метод для получения количества созданного приложения пользователями
	 *
	 * @param int $smart_app_id
	 *
	 * @return int
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function getActiveCountBySmartAppId(int $smart_app_id):int {

		// запрос проверен на EXPLAIN (INDEX=smart_app_id.status)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `smart_app_id` = ?i AND `status` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $smart_app_id, Domain_SmartApp_Entity_SmartAppUserRel::STATUS_ENABLE, 1);

		return $row["count"];
	}

	/**
	 * метод для получения количества созданных приложений пользователем
	 *
	 * @param int $user_id
	 *
	 * @return int
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function getActiveCountByUserId(int $user_id):int {

		// запрос проверен на EXPLAIN (INDEX=user_id.status)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `user_id` = ?i AND `status` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $user_id, Domain_SmartApp_Entity_SmartAppUserRel::STATUS_ENABLE, 1);

		return $row["count"];
	}

	/**
	 * метод для получения созданных приложений пользователем
	 *
	 * @param int $user_id
	 * @param int $limit
	 *
	 * @return Struct_Db_CompanyData_SmartAppUserRel[]
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function getActiveListByUserId(int $user_id, int $limit):array {

		// запрос проверен на EXPLAIN (INDEX=user_id.status)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `status` = ?i LIMIT ?i";
		$list  = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $user_id, Domain_SmartApp_Entity_SmartAppUserRel::STATUS_ENABLE, $limit);

		return self::_formatList($list);
	}

	/**
	 * метод для получения записи под обновление
	 *
	 * @param int $smart_app_id
	 * @param int $user_id
	 *
	 * @return Struct_Db_CompanyData_SmartAppUserRel
	 * @throws DBShardingNotFoundException
	 * @throws Domain_SmartApp_Exception_SmartAppNotFound
	 * @throws QueryFatalException
	 */
	public static function getForUpdate(int $smart_app_id, int $user_id):Struct_Db_CompanyData_SmartAppUserRel {

		// формируем и осуществляем запрос
		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `smart_app_id` = ?i AND `user_id` = ?i LIMIT ?i FOR UPDATE";
		$row   = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $smart_app_id, $user_id, 1);

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
	 * @return Struct_Db_CompanyData_SmartAppUserRel[]
	 */
	protected static function _formatList(array $list):array {

		return array_map([self::class, "_formatRow"], $list);
	}

	// форматируем одну запись из базы
	protected static function _formatRow(array $row):Struct_Db_CompanyData_SmartAppUserRel {

		if (empty($row)) {
			throw new Domain_SmartApp_Exception_SmartAppNotFound("smart app is not found");
		}
		return Struct_Db_CompanyData_SmartAppUserRel::rowToStruct($row);
	}
}