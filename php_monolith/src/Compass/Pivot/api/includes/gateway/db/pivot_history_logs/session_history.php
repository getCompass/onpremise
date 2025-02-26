<?php

namespace Compass\Pivot;

/**
 * класс-интерфейс для таблицы pivot_history_logs_{Y}.session_history
 */
class Gateway_Db_PivotHistoryLogs_SessionHistory extends Gateway_Db_PivotHistoryLogs_Main {

	protected const _TABLE_KEY = "session_history";

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
		int    $status,
		int    $login_at,
		int    $logout_at,
		string $ua_hash,
		string $ip_address,
		array  $extra
	):string {

		$shard_key = self::_getDbKey(self::getShardIdByTime($logout_at));

		$insert = [
			"session_uniq" => $session_uniq,
			"user_id"      => $user_id,
			"status"       => $status,
			"login_at"     => $login_at,
			"logout_at"    => $logout_at,
			"ua_hash"      => $ua_hash,
			"ip_address"   => $ip_address,
			"extra"        => $extra,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert(self::_TABLE_KEY, $insert);
	}

	/**
	 * Вставить массив записей
	 *
	 */
	public static function insertArray(int $shard_id, array $insert_list):int {

		$shard_key = self::_getDbKey($shard_id);

		return ShardingGateway::database($shard_key)->insertArray(self::_TABLE_KEY, $insert_list);
	}

	/**
	 * Получить список записей по шарду и id пользователя
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Gateway\DBShardingNotFoundException
	 * @throws \BaseFrame\Exception\Gateway\QueryFatalException
	 */
	public static function getListByShardAndUserId(int $shard_id, int $user_id):array {

		$db_key = self::_getDbKey($shard_id);

		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, self::_TABLE_KEY, $user_id, 1);

		// формируем и осуществляем запрос
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i LIMIT ?i";
		$list  = ShardingGateway::database($db_key)->getAll($query, self::_TABLE_KEY, $user_id, $row["count"]);

		$struct_list = [];
		foreach ($list as $row) {

			$struct_list[] = new Struct_Db_PivotHistoryLogs_SessionHistory(
				$row["session_uniq"],
				$user_id,
				$row["status"],
				$row["login_at"],
				$row["logout_at"],
				$row["ua_hash"],
				$row["ip_address"],
				fromJson($row["extra"])
			);
		}
		return $struct_list;
	}
}