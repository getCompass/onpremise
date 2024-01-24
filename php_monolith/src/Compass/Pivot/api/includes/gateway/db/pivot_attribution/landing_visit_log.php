<?php

namespace Compass\Pivot;

/**
 * класс для работы с таблицей pivot_attribution . landing_visit_log
 * @package Compass\Pivot
 */
class Gateway_Db_PivotAttribution_LandingVisitLog extends Gateway_Db_PivotAttribution_Main {

	protected const _TABLE_KEY = "landing_visit_log";

	/** @var int лимит выборки записей */
	protected const _LIMIT = 3000;

	/**
	 * Создаем запись
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PivotAttribution_LandingVisit $landing_visit):void {

		$insert = [
			"visit_id"            => $landing_visit->visit_id,
			"guest_id"            => $landing_visit->guest_id,
			"link"                => $landing_visit->link,
			"utm_tag"             => $landing_visit->utm_tag,
			"source_id"           => $landing_visit->source_id,
			"ip_address"          => $landing_visit->ip_address,
			"platform"            => $landing_visit->platform,
			"platform_os"         => $landing_visit->platform_os,
			"timezone_utc_offset" => $landing_visit->timezone_utc_offset,
			"screen_avail_width"  => $landing_visit->screen_avail_width,
			"screen_avail_height" => $landing_visit->screen_avail_height,
			"visited_at"          => $landing_visit->visited_at,
			"created_at"          => $landing_visit->created_at,
		];
		ShardingGateway::database(self::_DB_KEY)->insert(self::_TABLE_KEY, $insert);
	}

	/**
	 * Получаем запись по PK
	 *
	 * @return Struct_Db_PivotAttribution_LandingVisit
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function get(string $visit_id):Struct_Db_PivotAttribution_LandingVisit {

		// запрос проверен на explain (PRIMARY_KEY)
		$query = "SELECT * FROM `?p` WHERE `visit_id` = ?s LIMIT ?i";
		$row = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $visit_id, 1);

		return Struct_Db_PivotAttribution_LandingVisit::rowToStruct($row);
	}

	/**
	 * Получаем список записей за переданный период
	 * Записи в результате не упорядочены!
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function getListByPeriod(int $start_id, int $end_at):array {

		// запрос проверен на explain (visited_at)
		$query = "SELECT * FROM `?p` WHERE `visited_at` >= ?i AND `visited_at` <= ?i LIMIT ?i";
		$list = ShardingGateway::database(self::_DB_KEY)->getAll($query, self::_TABLE_KEY, $start_id, $end_at, self::_LIMIT);

		return array_map(static fn(array $row) => Struct_Db_PivotAttribution_LandingVisit::rowToStruct($row), $list);
	}

	/**
	 * Удаляем старые записи
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function deleteOlder(int $older_than_timestamp):void {

		// запрос проверен на EXPLAIN (INDEX=visited_at)
		$query = "DELETE FROM `?p` WHERE `visited_at` < ?i LIMIT ?i";
		ShardingGateway::database(self::_DB_KEY)->delete($query, self::_TABLE_KEY, $older_than_timestamp, 1000);
	}
}