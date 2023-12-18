<?php

namespace Compass\Company;

/**
 * Класс-интерфейс для базы данных company_system.member_activity_list
 */
class Gateway_Db_CompanySystem_MemberActivityList extends Gateway_Db_CompanySystem_Main {

	protected const _TABLE_KEY = "member_activity_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// получаем активность пользователя за день
	public static function get(int $user_id, int $day_start_at):array {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `user_id` = ?i AND `day_start_at` = ?i LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::getTableName(), $user_id, $day_start_at, 1);
		if (!isset($row["user_id"])) {
			throw new \BaseFrame\Exception\Gateway\RowNotFoundException("user activity not found");
		}

		return $row;
	}

	// получаем количество активных пользователей за промежуток времени
	public static function getCountListByDate(int $date_from_at, int $date_to_at, bool $is_assoc = false):array {

		// запрос проверен на EXPLAIN (INDEX=day_start_at)
		$limit    = ($date_to_at - $date_from_at) / DAY1;
		$query    = "SELECT `day_start_at`, COUNT(*) as `count` FROM `?p` WHERE `day_start_at` BETWEEN ?i AND ?i GROUP BY `day_start_at` LIMIT ?i";
		$row_list = ShardingGateway::database(self::_getDbKey())->getAll($query, self::getTableName(), $date_from_at, $date_to_at, $limit);
		if (!$is_assoc) {
			return $row_list;
		}

		$output = [];
		foreach ($row_list as $row) {
			$output[$row["day_start_at"]] = $row["count"];
		}

		return $output;
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	// функция возвращает название таблицы
	public static function getTableName():string {

		return self::_TABLE_KEY;
	}
}