<?php

namespace Compass\Pivot;

/**
 * Класс для работы с таблицей pivot_system.phphooker_queue
 */
class Gateway_Db_PivotSystem_PhphookerQueue extends Gateway_Db_PivotSystem_Main {

	/** @var string имя таблицы */
	protected const _TABLE_KEY = "phphooker_queue";

	/**
	 * Получение количества записей
	 *
	 * @return int
	 */
	public static function getTotalCount():int {

		// запрос проверен на EXPLAIN (INDEX=need_work)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE TRUE LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, 1);
		return $row["count"];
	}

	/**
	 * Получение количества истекших записей
	 *
	 * @param int $need_work
	 *
	 * @return int
	 */
	public static function getExpiredCount(int $need_work):int {

		// запрос проверен на EXPLAIN (INDEX=need_work)
		$query = "SELECT COUNT(*) as `count` FROM `?p` WHERE `need_work` < ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $need_work, 1);
		return $row["count"];
	}
}