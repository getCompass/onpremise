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
}