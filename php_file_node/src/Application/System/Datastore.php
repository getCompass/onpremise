<?php declare(strict_types = 1);

namespace Application\System;

/**
 * Класс для хранения всяких разных данных в рамках работы системы
 */
class Datastore {

	protected const _DB_KEY    = "system_file_node";
	protected const _TABLE_KEY = "datastore";

	protected const _CONF = [
		"mysql" => [
			"host" => MYSQL_SYSTEM_HOST,
			"user" => MYSQL_SYSTEM_USER,
			"pass" => MYSQL_SYSTEM_PASS,
			"ssl"  => false,
		],
		"db"    => self::_DB_KEY,
	];

	// получить значение по первичному ключу
	public static function get(string $key):array {

		$info = \sharding::configuredPDO(self::_CONF)
			->getOne("SELECT * FROM `?p` WHERE `key`=?s LIMIT ?i", self::_TABLE_KEY, $key, 1);
		return isset($info["extra"]) ? fromJson($info["extra"]) : [];
	}

	// вставить/обновить запись
	public static function set(string $key, array $set):bool {

		\sharding::configuredPDO(self::_CONF)->insertOrUpdate(self::_TABLE_KEY, [
			"key"   => $key,
			"extra" => toJson($set),
		]);

		return true;
	}
}