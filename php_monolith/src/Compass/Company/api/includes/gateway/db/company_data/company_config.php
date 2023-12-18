<?php

namespace Compass\Company;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Интерфейс для работы с таблицк company_data.company_config
 */
class Gateway_Db_CompanyData_CompanyConfig extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "company_config";

	/**
	 * метод вставки записи в базу
	 *
	 * @param Struct_Db_CompanyData_CompanyConfig $config
	 *
	 * @return string
	 * @throws ParseFatalException
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_CompanyData_CompanyConfig $config):string {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		$insert = [
			"key"        => $config->key,
			"created_at" => $config->created_at,
			"updated_at" => $config->updated_at,
			"value"      => $config->value,
		];

		// осуществляем запрос
		return ShardingGateway::database($shard_key)->insert($table_name, $insert);
	}

	/**
	 * Получение конфига из БД
	 */
	public static function get(string $key):array {

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `key` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $key, 1);

		if (!isset($row["key"])) {
			return [];
		}
		$row["value"] = fromJson($row["value"]);

		return $row["value"];
	}

	/**
	 * Получение списка конфигов из БД
	 */
	public static function getList(array $key_list):array {

		$output = [];

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query  = "SELECT * FROM `?p` WHERE `key` IN (?a) LIMIT ?i";
		$result = ShardingGateway::database(self::_getDbKey())->getAll($query, self::_TABLE_KEY, $key_list, count($key_list));

		foreach ($result as $row) {
			$output[$row["key"]] = fromJson($row["value"]);
		}

		return $output;
	}

	/**
	 * Установить атрибут
	 *
	 * @param string $key
	 * @param array  $set
	 *
	 * @return bool
	 * @throws ParseFatalException
	 */
	public static function insertOrUpdate(string $key, array $set):bool {

		$shard_key  = self::_getDbKey();
		$table_name = self::_getTableKey();

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_CompanyData_CompanyConfig::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		$set["key"] = $key;

		return ShardingGateway::database($shard_key)->insertOrUpdate($table_name, $set);
	}

	/**
	 * Получить имя таблицы
	 */
	protected static function _getTableKey():string {

		return static::_TABLE_KEY;
	}
}
