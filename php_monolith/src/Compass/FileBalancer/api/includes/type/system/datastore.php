<?php

namespace Compass\FileBalancer;

use Compass\FileBalancer\ShardingGateway;

/**
 * класс для хранения всяких разных данных в рамках работы системы
 */
class Type_System_Datastore {

	protected const _TABLE_KEY = "datastore";

	// получить значение по первичному ключу
	public static function get(string $key):array {

		$info = ShardingGateway::database(self::_getDbKey())
			->getOne("SELECT * FROM `?p` WHERE `key`=?s LIMIT ?i", self::_TABLE_KEY, self::_getKey($key), 1);
		return isset($info["extra"]) ? fromJson($info["extra"]) : [];
	}

	// вставить/обновить запись
	public static function set(string $key, array $set):bool {

		ShardingGateway::database(self::_getDbKey())->insertOrUpdate(self::_TABLE_KEY, [
			"key"   => self::_getKey($key),
			"extra" => toJson($set),
		]);

		return true;
	}

	// получаем key
	protected static function _getKey(string $key):string {

		return CURRENT_SERVER . "_" . $key;
	}

	/**
	 * получаем базу
	 *
	 * @return string
	 * @throws parseException
	 */
	protected static function _getDbKey():string {

		if (defined("CURRENT_SERVER") && CURRENT_SERVER == CLOUD_SERVER) {
			return "system_compass_company";
		}

		return getFileDbPrefix() . "_system";
	}
}
