<?php

namespace Compass\Jitsi;

/**
 * класс для хранения всяких разных данных в рамках работы системы
 */
class Type_System_Datastore {

	protected const _DB_KEY    = "pivot_system";
	protected const _TABLE_KEY = "datastore";

	/**
	 * получить значение по первичному ключу
	 *
	 */
	public static function get(string $key):array {

		$info = ShardingGateway::database(self::_DB_KEY)
			->getOne("SELECT * FROM `?p` WHERE `key`=?s LIMIT ?i", self::_TABLE_KEY, $key, 1);
		return isset($info["extra"]) ? fromJson($info["extra"]) : [];
	}

	/**
	 * вставить/обновить запись
	 *
	 */
	public static function set(string $key, array $set):bool {

		ShardingGateway::database(self::_DB_KEY)->insertOrUpdate(self::_TABLE_KEY, [
			"key"   => $key,
			"extra" => toJson($set),
		]);

		return true;
	}
}
