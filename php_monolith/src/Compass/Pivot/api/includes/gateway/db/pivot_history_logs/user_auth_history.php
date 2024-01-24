<?php

namespace Compass\Pivot;

/**
 * класс-интерфейс для таблицы pivot_history_logs_{Y}.user_auth_history
 */
class Gateway_Db_PivotHistoryLogs_UserAuthHistory extends Gateway_Db_PivotHistoryLogs_Main {

	protected const _TABLE_KEY = "user_auth_history";

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
		string $auth_map,
		int    $user_id,
		int    $status,
		int    $created_at,
		int    $updated_at
	):string {

		$shard_key = self::_getDbKey(self::getShardIdByTime($created_at));

		$insert = [
			"auth_map"   => $auth_map,
			"user_id"    => $user_id,
			"status"     => $status,
			"created_at" => $created_at,
			"updated_at" => $updated_at,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert(self::_TABLE_KEY, $insert);
	}
}