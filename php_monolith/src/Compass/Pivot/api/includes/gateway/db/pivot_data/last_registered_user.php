<?php

namespace Compass\Pivot;

/**
 * Класс-интерфейс для работы с таблицей последних регистраций.
 */
class Gateway_Db_PivotData_LastRegisteredUser extends Gateway_Db_PivotData_Main {

	protected const _TABLE_KEY = "last_registered_user";

	/**
	 * Вставляет одну запись.
	 */
	public static function insert(int $user_id, int $partner_id, array $extra):void {

		$insert = [
			"user_id"    => $user_id,
			"partner_id" => $partner_id,
			"created_at" => time(),
			"updated_at" => 0,
			"extra"      => toJson($extra),
		];

		ShardingGateway::database(static::_DB_KEY)->insert(static::_TABLE_KEY, $insert);
	}

	/**
	 * Вставляет одну запись.
	 */
	public static function setPartnerId(int $user_id, int $partner_id):void {

		$update = [
			"user_id"    => $user_id,
			"partner_id" => $partner_id,
			"updated_at" => time(),
		];

		// EXPLAIN: INDEX PRIMARY
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i LIMIT ?i";
		ShardingGateway::database(static::_DB_KEY)->update($query, static::_TABLE_KEY, $update, $user_id, 1);
	}

	/**
	 * Возвращает последние регистрации пользователей для указанного партнера.
	 * @return Struct_Db_PivotData_LastRegisteredUser[]
	 */
	public static function getLastByPartnerId(int $partner_id, int $limit = 100, int $offset = 0):array {

		// EXPLAIN: INDEX get_by_partner
		$query  = "SELECT * FROM `?p` WHERE `partner_id` = ?i ORDER BY `created_at` DESC LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database(static::_DB_KEY)->getAll($query, static::_TABLE_KEY, $partner_id, $limit, $offset);

		return static::_toStructList($result);
	}

	/**
	 * Возвращает последние регистрации пользователей для указанного партнера.
	 * @return Struct_Db_PivotData_LastRegisteredUser[]
	 */
	public static function getList(array $user_id_list):array {

		// EXPLAIN: INDEX PRIMARY
		$query  = "SELECT * FROM `?p` WHERE `user_id` IN (?a) LIMIT ?i";
		$result = ShardingGateway::database(static::_DB_KEY)->getAll($query, static::_TABLE_KEY, $user_id_list, count($user_id_list));

		return static::_toStructList($result);
	}

	# region protected

	/**
	 * Конвертирует массив записей базы в массив структур.
	 * @return Struct_Db_PivotData_LastRegisteredUser[]
	 */
	protected static function _toStructList(array $raw_list):array {

		return array_map(static fn(array $el) => static::_toStruct($el), $raw_list);
	}

	/**
	 * Конвертирует запись базы в структуру.
	 */
	protected static function _toStruct(array $raw):Struct_Db_PivotData_LastRegisteredUser {

		$raw["extra"] = fromJson($raw["extra"]);
		return new Struct_Db_PivotData_LastRegisteredUser(...$raw);
	}

	# endregion protected
}
