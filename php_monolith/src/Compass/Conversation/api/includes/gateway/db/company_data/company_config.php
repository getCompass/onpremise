<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Интерфейс для работы с таблицк company_data.company_config
 */
class Gateway_Db_CompanyData_CompanyConfig extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "company_config";

	/**
	 * метод вставки записи в базу
	 *
	 * @throws \queryException
	 */
	public static function insert(Struct_Db_CompanyData_CompanyConfig $config):string {

		$table_name = self::_getTableKey();

		$insert = [
			"key"        => $config->key,
			"created_at" => $config->created_at,
			"updated_at" => $config->updated_at,
			"value"      => $config->value,
		];

		// осуществляем запрос
		return static::_connect()->insert($table_name, $insert);
	}

	/**
	 * Получение конфига из БД
	 *
	 */
	public static function get(string $key):array {

		// запрос проверен на EXPLAIN(INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `key` = ?s LIMIT ?i";
		$row   = static::_connect()->getOne($query, self::_TABLE_KEY, $key, 1);

		if (!isset($row["key"])) {
			return [];
		}
		$row["value"] = fromJson($row["value"]);

		return $row["value"];
	}

	/**
	 * Установить атрибут
	 *
	 * @throws \parseException
	 */
	public static function set(string $key, array $set):bool {

		$table_name = self::_getTableKey();

		foreach ($set as $field => $_) {

			if (!property_exists(Struct_Db_CompanyData_CompanyConfig::class, $field)) {
				throw new ParseFatalException("send unknown field");
			}
		}

		$set["key"] = $key;

		return static::_connect()->insertOrUpdate($table_name, $set);
	}

	/**
	 * Получить имя таблицы
	 *
	 */
	protected static function _getTableKey():string {

		return static::_TABLE_KEY;
	}
}
