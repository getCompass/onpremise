<?php

namespace Compass\Pivot;

/**
 * Интерфейс для работы с БД конфига
 */
class Gateway_Db_PivotData_PivotConfig extends Gateway_Db_PivotData_Main {

	protected const _TABLE_KEY = "pivot_config";

	/**
	 * Получение конфига из БД
	 *
	 */
	public static function get(string $key):array {

		// запрос проверен на EXPLAIN
		$query = "SELECT * FROM `?p` WHERE `key` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $key, 1);

		if (!isset($row["key"])) {
			return [];
		}

		return fromJson($row["value"]);
	}

	/**
	 * Установить атрибут
	 *
	 */
	public static function set(string $key, array $value):void {

		// запрос проверен на EXPLAIN
		ShardingGateway::database(self::_getDbKey())->insertOrUpdate(self::_TABLE_KEY, [
			"key"   => $key,
			"value" => $value,
		]);
	}

	/**
	 * Удаление атрибута
	 *
	 */
	public static function delete(string $key):void {

		// запрос проверен на EXPLAIN
		ShardingGateway::database(self::_getDbKey())->delete("DELETE FROM `?p` WHERE `key` = ?s LIMIT ?i", self::_TABLE_KEY, $key, 1);
	}
}