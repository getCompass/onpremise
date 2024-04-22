<?php

namespace Compass\Premise;

/**
 * класс для работы с таблицей pivot_system . auto_increment
 * @package Compass\Premise
 */
class Gateway_Db_PivotSystem_AutoIncrement {

	public const USER_ID_KEY = "user_id";    // ключ для user_id

	protected const _DB_KEY    = "pivot_system";
	protected const _TABLE_KEY = "auto_increment";

	/**
	 * Метод для получения значения
	 *
	 * @param string $key
	 *
	 * @return int
	 * @throws \queryException
	 */
	public static function get(string $key):int {

		$query  = "SELECT * FROM `?p` WHERE `key` = ?s LIMIT ?i";
		$result = ShardingGateway::database(self::_DB_KEY)->getOne($query, self::_TABLE_KEY, $key, 1);

		// если ничего не нашли
		if (!isset($result["key"])) {
			return 0;
		}

		return $result["value"];
	}
}