<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * класс-интерфейс для таблицы pivot_user_{10m}.session_active_list_1
 */
class Gateway_Db_PivotUser_SessionActiveList extends Gateway_Db_PivotUser_Main {

	protected const _TABLE_KEY = "session_active_list";

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
		string $session_uniq,
		int    $created_at,
		int    $updated_at,
		int    $login_at,
		int    $refreshed_at,
		int    $last_online_at,
		string $ua_hash,
		string $ip_address,
		array  $extra
	):string {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		$insert = [
			"user_id"        => $user_id,
			"session_uniq"   => $session_uniq,
			"created_at"     => $created_at,
			"updated_at"     => $updated_at,
			"login_at"       => $login_at,
			"refreshed_at"   => $refreshed_at,
			"last_online_at" => $last_online_at,
			"ua_hash"        => $ua_hash,
			"ip_address"     => $ip_address,
			"extra"          => $extra,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * метод для обновления записи
	 *
	 * @throws \parseException
	 */
	public static function set(int $user_id, string $session_uniq, array $set):int {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_PivotUser_SessionActive::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		// формируем и осуществляем запрос
		$query = "UPDATE `?p` SET ?u WHERE `user_id` = ?i AND `session_uniq` = ?s LIMIT ?i";
		return ShardingGateway::database($shard_key)->update($query, $table_name, $set, $user_id, $session_uniq, 1);
	}

	/**
	 * метод для получения записи пользователя
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(int $user_id, string $session_uniq):Struct_Db_PivotUser_SessionActive {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос. user_id в запросе во имя безопасности
		$query = "SELECT * FROM `?p` WHERE `session_uniq`=?s AND `user_id`=?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $session_uniq, $user_id, 1);
		if (!isset($row["session_uniq"])) {
			throw new \cs_RowIsEmpty();
		}

		return new Struct_Db_PivotUser_SessionActive(
			$session_uniq,
			$user_id,
			$row["created_at"],
			$row["updated_at"],
			$row["login_at"],
			$row["refreshed_at"],
			$row["last_online_at"],
			$row["ua_hash"],
			$row["ip_address"],
			fromJson($row["extra"])
		);
	}

	/**
	 * метод для получения несколько записей пользователя
	 *
	 * @return Struct_Db_PivotUser_SessionActive[]
	 * @throws \BaseFrame\Exception\Gateway\DBShardingNotFoundException
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 */
	public static function getList(int $user_id, array $session_uniq_list):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос. user_id в запросе во имя безопасности
		$query = "SELECT * FROM `?p` WHERE `session_uniq` IN (?a) AND `user_id`=?i LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $session_uniq_list, $user_id, count($session_uniq_list));

		$struct_list = [];
		foreach ($list as $row) {

			$struct_list[] = new Struct_Db_PivotUser_SessionActive(
				$row["session_uniq"],
				$user_id,
				$row["created_at"],
				$row["updated_at"],
				$row["login_at"],
				$row["refreshed_at"],
				$row["last_online_at"],
				$row["ua_hash"],
				$row["ip_address"],
				fromJson($row["extra"])
			);
		}

		return $struct_list;
	}

	/**
	 * метод для получения записией с активными сессиями пользователя
	 *
	 * @return Struct_Db_PivotUser_SessionActive[]
	 */
	public static function getActiveSessionList(int $user_id):array {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// получаем количество записей с активными сессиями пользователя
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($shard_key)->getOne($query, $table_name, $user_id, 1);

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$list  = ShardingGateway::database($shard_key)->getAll($query, $table_name, $user_id, $row["count"]);

		$struct_list = [];
		foreach ($list as $row) {

			$struct_list[] = new Struct_Db_PivotUser_SessionActive(
				$row["session_uniq"],
				$user_id,
				$row["created_at"],
				$row["updated_at"],
				$row["login_at"],
				$row["refreshed_at"],
				$row["last_online_at"],
				$row["ua_hash"],
				$row["ip_address"],
				fromJson($row["extra"])
			);
		}
		return $struct_list;
	}

	/**
	 * метод для удаление записи
	 *
	 */
	public static function delete(int $user_id, string $session_uniq):int {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос. Тут удаляем оставляем только в истории сессию для исключение проблем наверняка
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i AND `session_uniq` = ?s LIMIT ?i";
		return ShardingGateway::database($shard_key)->delete($query, $table_name, $user_id, $session_uniq, 1);
	}

	/**
	 * метод для удаление массива записей
	 *
	 */
	public static function deleteArray(int $user_id, array $session_uniq_list):int {

		$shard_key  = self::_getDbKey($user_id);
		$table_name = self::_getTableKey($user_id);

		// формируем и осуществляем запрос
		// проверил запрос в EXPLAIN: key=PRIMARY
		$query = "DELETE FROM `?p` WHERE `user_id` = ?i AND `session_uniq` IN (?a) LIMIT ?i";
		return ShardingGateway::database($shard_key)->delete($query, $table_name, $user_id, $session_uniq_list, count($session_uniq_list));
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// получает таблицу
	protected static function _getTableKey(int $user_id):string {

		return self::_TABLE_KEY . "_" . ceil($user_id / 1000000);
	}
}