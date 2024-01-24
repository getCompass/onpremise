<?php

namespace Compass\Thread;

/**
 * класс-интерфейс к таблице cloud_thread_{Year}.thread_follower_list
 */
class Gateway_Db_CompanyThread_ThreadFollowerList extends Gateway_Db_CompanyThread_Main {

	protected const _TABLE_KEY = "thread_follower_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод для создания записи
	public static function insert(array $insert):void {

		ShardingGateway::database(self::_getDbKey())->insert(self::_getTable(), $insert);
	}

	// метод для обновления записи
	public static function set(string $thread_map, array $set):void {

		ShardingGateway::database(self::_getDbKey())->update("UPDATE `?p` SET ?u WHERE `thread_map` = ?s LIMIT ?i",
			self::_getTable(), $set, $thread_map, 1);
	}

	// метод для получения записи
	public static function getOne(string $thread_map):array {

		$follower_row = ShardingGateway::database(self::_getDbKey())->getOne("SELECT * FROM `?p` WHERE `thread_map` = ?s LIMIT ?i",
			self::_getTable(), $thread_map, 1);

		return self::_formatRow($follower_row);
	}

	// метод для получения записи
	public static function getList(array $thread_map_list):array {

		// запрос проверен на EXPLAIN (INDEX=`PRIMARY`)
		$query = "SELECT * FROM `?p` WHERE `thread_map` IN (?a) LIMIT ?i";

		$follower_list = ShardingGateway::database(self::_getDbKey())->getAll($query, self::_getTable(), $thread_map_list, count($thread_map_list));

		// приводим к формату
		foreach ($follower_list as $index => $follower_row) {
			$follower_list[$index] = self::_formatRow($follower_row);
		}

		return $follower_list;
	}

	// метод для получения записи на обновление
	public static function getForUpdate(string $thread_map):array {

		$follower_row = ShardingGateway::database(self::_getDbKey())->getOne("SELECT * FROM `?p` WHERE `thread_map` = ?s LIMIT ?i FOR UPDATE",
			self::_getTable(), $thread_map, 1);

		return self::_formatRow($follower_row);
	}

	// -------------------------------------------------------
	// методы для работы с JSON полем follower_list
	// -------------------------------------------------------

	/*
	 * структура follower_list
	 *
	 * Array
	 * (
	 *    [5] => Array
	 *          (
	 *                [version] => 1
	 *                [created_at] => 1531214606
	 *          )
	 * )
	 */

	protected const _FOLLOWER_VERSION = 1;
	protected const _FOLLOWER_SCHEMA  = [
		"created_at" => 0,
	];

	// возвращает временную метку создания записи
	public static function getFollowerCreatedAt(array $follower_item):int {

		// актуализируем user_schema
		$follower_item = self::_getFollowerSchema($follower_item);

		return $follower_item["created_at"];
	}

	// создать новую структуру для users
	public static function initFollowerSchema():array {

		// получаем текущую схему users
		$follower_item = self::_FOLLOWER_SCHEMA;

		// устанавливаем персональные параметры
		$follower_item["created_at"] = time();

		// устанавливаем текущую версию
		$follower_item["version"] = self::_FOLLOWER_VERSION;

		return $follower_item;
	}

	// получить актуальную структуру для users
	protected static function _getFollowerSchema(array $follower_item):array {

		// если версия не совпадает - дополняем её до текущей
		if ($follower_item["version"] != self::_FOLLOWER_VERSION) {

			$follower_item            = array_merge(self::_FOLLOWER_SCHEMA, $follower_item);
			$follower_item["version"] = self::_FOLLOWER_VERSION;
		}

		return $follower_item;
	}

	// -------------------------------------------------------
	// методы для работы с JSON полем unfollower_list
	// -------------------------------------------------------

	/*
	 * структура unfollower_list
	 *
	 * Array
	 * (
	 *    [5] => Array
	 *          (
	 *                [version] => 1
	 *                [created_at] => 1531214606
	 *          )
	 * )
	 */

	protected const _UNFOLLOWER_VERSION = 1;
	protected const _UNFOLLOWER_SCHEMA  = [
		"created_at" => 0,
	];

	// возвращает временную метку создания записи
	public static function getUnfollowerCreatedAt(array $unfollower_item):int {

		// актуализируем user_schema
		$unfollower_item = self::_getUnfollowerSchema($unfollower_item);

		return $unfollower_item["created_at"];
	}

	// создать новую структуру для users
	public static function initUnfollowerSchema():array {

		// получаем текущую схему users
		$unfollower_item = self::_UNFOLLOWER_SCHEMA;

		// устанавливаем персональные параметры
		$unfollower_item["created_at"] = time();

		// устанавливаем текущую версию
		$unfollower_item["version"] = self::_UNFOLLOWER_VERSION;

		return $unfollower_item;
	}

	// получить актуальную структуру для users
	protected static function _getUnfollowerSchema(array $unfollower_item):array {

		// если версия не совпадает - дополняем её до текущей
		if ($unfollower_item["version"] != self::_UNFOLLOWER_VERSION) {

			$unfollower_item            = array_merge(self::_UNFOLLOWER_SCHEMA, $unfollower_item);
			$unfollower_item["version"] = self::_UNFOLLOWER_VERSION;
		}

		return $unfollower_item;
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// приводим к формату запись из таблицы
	protected static function _formatRow(array $follower_row):array {

		if (isset($follower_row["thread_map"])) {

			$follower_row["follower_list"]   = fromJson($follower_row["follower_list"]);
			$follower_row["unfollower_list"] = fromJson($follower_row["unfollower_list"]);
		}

		return $follower_row;
	}

	// получаем таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}