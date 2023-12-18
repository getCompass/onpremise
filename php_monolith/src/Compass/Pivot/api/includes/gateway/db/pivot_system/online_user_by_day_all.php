<?php

namespace Compass\Pivot;

/**
 * Интерфейс для работы с online_user_by_day
 */
class Gateway_Db_PivotSystem_OnlineUserByDayAll extends Gateway_Db_PivotSystem_Main {

	protected const _TABLE_KEY = "online_user_by_day_all";

	/**
	 * метод вставки записи в базу
	 *
	 * @param int $user_id
	 *
	 * @throws \queryException
	 */
	public static function insert(int $user_id):void {

		$shard_key = self::_getDbKey();

		$insert = [
			"day_at"  => dayStart(),
			"user_id" => $user_id,
		];

		// осуществляем запрос
		ShardingGateway::database($shard_key)->insert(self::_TABLE_KEY, $insert);
	}

	// получаем статистику за день
	public static function getStatByDay(int $limit = 14, int $offset = 0):array {

		$query  = "select day_at, count(user_id) as count from `?p` WHERE TRUE group by day_at order by day_at DESC LIMIT ?i OFFSET ?i";
		$result = ShardingGateway::database("pivot_system")->getAll($query, self::_TABLE_KEY, $limit, $offset);

		$output = [];
		foreach ($result as $row) {

			$output[] = [
				$row["day_at"], $row["count"],
			];
		}
		return $output;
	}
}
