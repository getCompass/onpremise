<?php declare(strict_types = 1);

namespace Compass\Premise;

class Gateway_Db_PremiseData_PremiseConfig extends Gateway_Db_PremiseData_Main {

	protected const _TABLE_KEY = "premise_config";

	/**
	 * Получить одну запись
	 *
	 * @param string $key
	 *
	 * @return Struct_Db_PremiseData_Config
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \cs_RowIsEmpty
	 */
	public static function getOne(string $key):Struct_Db_PremiseData_Config {

		$db_key    = self::_getDbKey();
		$table_key = self::_getTableKey();

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `key` = ?s LIMIT ?i";
		$row   = ShardingGateway::database($db_key)->getOne($query, $table_key, $key, 1);

		if (!isset($row["key"])) {
			throw new \cs_RowIsEmpty();
		}

		return self::_rowToObject($row);
	}

	/**
	 * Вставить запись
	 *
	 * @param Struct_Db_PremiseData_Config $config
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_PremiseData_Config $config):void {

		$insert_array = [
			"key"        => $config->key,
			"created_at" => $config->created_at,
			"updated_at" => $config->updated_at,
			"value"      => toJson($config->value),

		];

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		ShardingGateway::database(self::_getDbKey())->insert(self::_getTableKey(), $insert_array);
	}

	/**
	 * Изменить запись
	 *
	 * @param string $key
	 * @param array  $set
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function set(string $key, array $set):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `key` = ?s LIMIT ?i";
		ShardingGateway::database(self::_getDbKey())->update($query, self::_getTableKey(), $set, $key, 1);
	}

	/**
	 * Создаем структуру из строки БД
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_PremiseData_Config
	 */
	protected static function _rowToObject(array $row):Struct_Db_PremiseData_Config {

		return new Struct_Db_PremiseData_Config(
			$row["key"],
			$row["created_at"],
			$row["updated_at"],
			fromJson($row["value"]),
		);
	}

	/**
	 * Получаем таблицу
	 *
	 * @return string
	 */
	protected static function _getTableKey():string {

		return self::_TABLE_KEY;
	}
}
