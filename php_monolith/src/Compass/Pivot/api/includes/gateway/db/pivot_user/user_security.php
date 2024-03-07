<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_user_{10m}.user_security_{1}
 */
class Gateway_Db_PivotUser_UserSecurity extends Gateway_Db_PivotUser_Main {

	protected const _TABLE_KEY = "user_security";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 * @mixed
	 */
	public static function insert(
		int    $user_id,
		string $phone_number,
		string $mail,
		int    $created_at,
		int    $updated_at
	):string {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		$insert = [
			"user_id"      => $user_id,
			"phone_number" => $phone_number,
			"mail"         => $mail,
			"created_at"   => $created_at,
			"updated_at"   => $updated_at,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(int $user_id, array $set):int {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotUser_UserSecurity::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, 1);
	}

	/**
	 * метод для получения записи пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $user_id):Struct_Db_PivotUser_UserSecurity {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос. user_id в запросе во имя безопасности
		$query = "SELECT * FROM `?p` WHERE `user_id`=?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);
		if (!isset($row["user_id"])) {
			throw new \cs_RowIsEmpty();
		}

		return Struct_Db_PivotUser_UserSecurity::rowToStruct($row);
	}

	/**
	 * получаем список по массив идентификаторов
	 *
	 * @return Struct_Db_PivotUser_UserSecurity[]
	 */
	public static function getAllByList(array $user_id_list):array {

		$grouped_user_id_by_shard = [];
		foreach ($user_id_list as $user_id) {
			$grouped_user_id_by_shard[self::_getDbKey($user_id) . "." . self::_getTableKey($user_id)][] = $user_id;
		}

		$output = [];
		foreach ($grouped_user_id_by_shard as $shard => $grouped_user_id_list) {

			[$db_key, $table_key] = explode(".", $shard);

			// запрос проверен на EXPLAIN (INDEX=PRIMARY)
			$query = "SELECT * FROM `?p` WHERE `user_id` IN (?a) LIMIT ?i";
			$list  = ShardingGateway::database($db_key)->getAll($query, $table_key, $grouped_user_id_list, count($grouped_user_id_list));

			foreach ($list as $row) {

				$output[$row["user_id"]] = Struct_Db_PivotUser_UserSecurity::rowToStruct($row);
			}
		}

		return $output;
	}

	/**
	 * метод для удаления записи пользователя
	 */
	public static function delete(int $user_id):void {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		ShardingGateway::database($shard_key)->delete($query, $table_name, $user_id, 1);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получает таблицу
	protected static function _getTableKey(int $user_id):string {

		return self::_TABLE_KEY . "_" . ceil($user_id / 1000000);
	}
}