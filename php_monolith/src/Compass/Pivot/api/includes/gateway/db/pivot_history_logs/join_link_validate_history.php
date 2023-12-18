<?php

namespace Compass\Pivot;

/**
 * Класс-интерфейс для таблицы pivot_history_logs_{Y}.join_link_validate_history
 */
class Gateway_Db_PivotHistoryLogs_JoinLinkValidateHistory extends Gateway_Db_PivotHistoryLogs_Main {

	protected const _TABLE_KEY = "join_link_validate_history";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * Метод вставки записи в базу
	 *
	 * @throws \queryException
	 */
	public static function insert(int $user_id, string $join_link_uniq, string $session_uniq, string $link, array $extra):void {

		$created_at = time();
		$shard_key  = self::_getDbKey(self::getShardIdByTime($created_at));

		$insert = [
			"join_link_uniq" => $join_link_uniq,
			"user_id"        => $user_id,
			"session_uniq"   => $session_uniq,
			"input_link"     => $link,
			"created_at"     => $created_at,
			"extra"          => $extra,
		];

		// осуществляем запрос
		ShardingGateway::database($shard_key)->insert(self::_TABLE_KEY, $insert);
	}
}