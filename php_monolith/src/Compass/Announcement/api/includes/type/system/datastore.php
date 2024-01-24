<?php

namespace Compass\Announcement;

/**
 * Класс для хранения всяких разных данных в рамках работы системы
 */
class Type_System_Datastore {

	protected const _DB_KEY    = "announcement_service";
	protected const _TABLE_KEY = "datastore";

	// получить значение по первичному ключу
	public static function get(string $key):array {

		$query = "SELECT * FROM `?p` WHERE `key`=?s LIMIT ?i";
		$info  = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $key, 1);

		return isset($info["extra"]) ? fromJson($info["extra"]) : [];
	}

	// вставить/обновить запись
	public static function set(string $key, array $set):bool {

		ShardingGateway::database(self::_DB_KEY)->insertOrUpdate(self::_TABLE_KEY, [
			"key"   => $key,
			"extra" => toJson($set),
		]);

		return true;
	}
}
