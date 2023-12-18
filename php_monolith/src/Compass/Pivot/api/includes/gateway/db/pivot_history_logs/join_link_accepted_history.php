<?php

namespace Compass\Pivot;

/**
 * класс-интерфейс для таблицы pivot_history_logs_{Y}.join_link_accepted_history
 */
class Gateway_Db_PivotHistoryLogs_JoinLinkAcceptedHistory extends Gateway_Db_PivotHistoryLogs_Main {

	protected const _TABLE_KEY = "join_link_accepted_history";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 */
	public static function insert(string $join_link_uniq, int $user_id, int $company_id, int $entry_id, string $session_uniq, array $extra):void {

		$created_at = time();
		$shard_key  = self::_getDbKey(self::getShardIdByTime($created_at));

		$insert = [
			"join_link_uniq" => $join_link_uniq,
			"user_id"        => $user_id,
			"company_id"     => $company_id,
			"entry_id"       => $entry_id,
			"session_uniq"   => $session_uniq,
			"created_at"     => $created_at,
			"extra"          => $extra,
		];

		// осуществляем запрос
		ShardingGateway::database($shard_key)->insert(self::_TABLE_KEY, $insert);
	}
}