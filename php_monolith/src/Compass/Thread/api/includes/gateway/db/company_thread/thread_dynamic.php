<?php

namespace Compass\Thread;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;

/**
 * класс-интерфейс к таблице cloud_thread_{Year}.thread_dynamic
 */
class Gateway_Db_CompanyThread_ThreadDynamic extends Gateway_Db_CompanyThread_Main {

	protected const _TABLE_KEY = "thread_dynamic";

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
	public static function getOne(string $thread_map):Struct_Db_CompanyThread_ThreadDynamic {

		$dynamic_row = ShardingGateway::database(self::_getDbKey())->getOne("SELECT * FROM `?p` WHERE `thread_map` = ?s LIMIT ?i",
			self::_getTable(), $thread_map, 1);

		return self::_rowToObject($dynamic_row);
	}

	/**
	 * Возвращает все динамические данные для тредов.
	 *
	 * @param array $thread_map_list
	 * @param bool  $is_assoc
	 *
	 * @return Struct_Db_CompanyThread_ThreadDynamic[]
	 * @throws ParseFatalException
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function getAll(array $thread_map_list, bool $is_assoc = false):array {

		// EXPLAIN INDEX PRIMARY
		$query  = "SELECT * FROM `?p` WHERE `thread_map` IN (?a) LIMIT ?i";
		$result = ShardingGateway::database(self::_getDbKey())->getAll($query, self::_getTable(), $thread_map_list, count($thread_map_list));

		if (!$is_assoc) {
			return array_map(static fn(array $row) => self::_rowToObject($row), $result);
		}

		$assoc_result = [];
		foreach ($result as $row) {
			$assoc_result[$row["thread_map"]] = self::_rowToObject($row);
		}

		return $assoc_result;
	}

	// метод для получения записи на обновление
	public static function getForUpdate(string $thread_map):Struct_Db_CompanyThread_ThreadDynamic {

		$dynamic_row = ShardingGateway::database(self::_getDbKey())->getOne("SELECT * FROM `?p` WHERE `thread_map` = ?s LIMIT ?i FOR UPDATE",
			self::_getTable(), $thread_map, 1);

		return self::_rowToObject($dynamic_row);
	}

	# region методы для работы с JSON полем user_mute_info

	/*
	 * структура user_mute_info
	 *
	   * Array
	   * (
	   *     [1] => Array
	   *         (
	   *             [version] => 1
	   *             [is_muted] => 1
	   *         )
	   *
	   *     [2] => Array
	   *         (
	   *             [version] => 1
	   *             [is_muted] => 0
	   *         )
	   *
	   * )
	 */

	// версия упаковщика
	protected const _USER_MUTE_INFO_VERSION = 1;

	// схема каждого элемента в массиве
	protected const _USER_MUTE_INFO_ITEM_SCHEMA = [
		"is_muted" => 0,
	];

	// узнать в муте ли тред
	public static function isMuted(array $user_mute_info, int $user_id):bool {

		if (!isset($user_mute_info[$user_id])) {
			return false;
		}

		// актуализируем версию
		$user_mute_info_item = self::_getUserMuteInfoItem($user_mute_info[$user_id]);

		return $user_mute_info_item["is_muted"] == 1;
	}

	// меняем is_muted
	public static function setIsMuted(array $user_mute_info_item, bool $is_muted):array {

		// актуализируем версию
		$user_mute_info_item = self::_getUserMuteInfoItem($user_mute_info_item);

		// устанавливаем значение
		$user_mute_info_item["is_muted"] = $is_muted ? 1 : 0;

		return $user_mute_info_item;
	}

	// создать новую структуру для элемента в user_mute_info
	public static function initUserMuteInfoItem():array {

		$user_mute_info_item            = self::_USER_MUTE_INFO_ITEM_SCHEMA;
		$user_mute_info_item["version"] = self::_USER_MUTE_INFO_VERSION;

		return $user_mute_info_item;
	}

	// получить актуальную структуру для элемента в user_mute_info
	protected static function _getUserMuteInfoItem(array $user_mute_info_item):array {

		// если версия не совпадает - дополняем её до текущей
		if ($user_mute_info_item["version"] != self::_USER_MUTE_INFO_VERSION) {

			$user_mute_info_item            = array_merge(self::_USER_MUTE_INFO_ITEM_SCHEMA, $user_mute_info_item);
			$user_mute_info_item["version"] = self::_USER_MUTE_INFO_VERSION;
		}

		return $user_mute_info_item;
	}

	# endregion

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// преобразуем строку таблицы в объект структуры
	protected static function _rowToObject(array $row):Struct_Db_CompanyThread_ThreadDynamic {

		// приводим запись бд к формату
		$row = self::_formatRow($row);

		return Struct_Db_CompanyThread_ThreadDynamic::fromArray($row);
	}

	// приводим к формату запись из таблицы
	protected static function _formatRow(array $row):array {

		if (isset($row["thread_map"])) {

			$row["user_mute_info"]    = fromJson($row["user_mute_info"]);
			$row["user_hide_list"]    = fromJson($row["user_hide_list"]);
			$row["last_read_message"] = fromJson($row["last_read_message"]);
		}

		return $row;
	}

	// получаем таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}
