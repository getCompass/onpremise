<?php

namespace Compass\Pivot;

/**
 * класс-интерфейс для таблицы pivot_history_logs_{Y}.user_action_history
 */
class Gateway_Db_PivotHistoryLogs_UserActionHistory extends Gateway_Db_PivotHistoryLogs_Main {

	protected const _TABLE_KEY = "user_action_history";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод для создания записи
	 *
	 * @throws \queryException
	 */
	public static function insert(int $user_id, int $type, int $created_at, array $extra):string {

		$shard_key = self::_getDbKey(self::getShardIdByTime($created_at));

		$insert = [
			"user_id"    => $user_id,
			"type"       => $type,
			"created_at" => $created_at,
			"extra"      => $extra,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert(self::_TABLE_KEY, $insert);
	}
}