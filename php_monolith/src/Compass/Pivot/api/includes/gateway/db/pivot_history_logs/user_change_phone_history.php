<?php

namespace Compass\Pivot;

/**
 * класс-интерфейс для таблицы pivot_history_logs_{Y}.user_change_phone_history
 */
class Gateway_Db_PivotHistoryLogs_UserChangePhoneHistory extends Gateway_Db_PivotHistoryLogs_Main {

	protected const _TABLE_KEY = "user_change_phone_history";

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
		string $previous_phone_number,
		string $new_phone_number,
		string $change_phone_story_map,
		int    $created_at,
		int    $updated_at
	):string {

		$shard_key = self::_getDbKey(self::getShardIdByTime($created_at));

		$insert = [
			"user_id"                => $user_id,
			"created_at"             => $created_at,
			"updated_at"             => $updated_at,
			"previous_phone_number"  => $previous_phone_number,
			"new_phone_number"       => $new_phone_number,
			"change_phone_story_map" => $change_phone_story_map,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert(self::_TABLE_KEY, $insert);
	}
}