<?php

namespace Compass\Pivot;

/**
 * Интерфейс для работы с БД списков компаний
 */
class Gateway_Db_PivotSystem_DefaultFileList extends Gateway_Db_PivotSystem_Main {

	protected const _TABLE_KEY = "default_file_list";

	/**
	 * Получение запись
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function get(string $dictionary_key):Struct_Db_PivotSystem_DefaultFile {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "SELECT * FROM `?p` WHERE `dictionary_key` = ?s LIMIT ?i";
		$row   = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, $dictionary_key, 1);

		if (!isset($row["dictionary_key"])) {
			throw new \cs_RowIsEmpty();
		}
		return new Struct_Db_PivotSystem_DefaultFile(
			$row["dictionary_key"],
			$row["file_key"],
			$row["file_hash"],
			fromJson($row["extra"]),
		);
	}

	/**
	 * Получение списка записей
	 *
	 */
	public static function getList(array $dictionary_key_list):array {

		$output = [];

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query  = "SELECT * FROM `?p` WHERE `dictionary_key` IN (?a) LIMIT ?i";
		$result = ShardingGateway::database(self::_getDbKey())->getAll($query, self::_TABLE_KEY, $dictionary_key_list, count($dictionary_key_list));

		foreach ($result as $row) {
			$output[$row["dictionary_key"]] = self::_convertRowToStruct($row);
		}

		return $output;
	}

	/**
	 * Получение количества записей
	 */
	public static function getCount():int {

		assertTestServer();

		// только для чистки мира и тестовых серверов (без EXPLAIN)
		$query  = "SELECT COUNT(*) AS `count` FROM `?p` WHERE TRUE LIMIT ?i";
		$result = ShardingGateway::database(self::_getDbKey())->getOne($query, self::_TABLE_KEY, 1);

		return $result["count"];
	}

	/**
	 * Установить запись
	 *
	 */
	public static function set(string $dictionary_key, string $file_key, string $file_hash, array $extra = []):void {

		ShardingGateway::database(self::_getDbKey())->insertOrUpdate(self::_TABLE_KEY, [
			"dictionary_key" => $dictionary_key,
			"file_key"       => $file_key,
			"file_hash"      => $file_hash,
			"extra"          => toJson($extra),
		]);
	}

	/**
	 * Обновляем запись
	 *
	 * @param string $dictionary_key
	 * @param array  $set
	 *
	 * @return int
	 */
	public static function update(string $dictionary_key, array $set):int {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE `dictionary_key` = ?s LIMIT ?i";
		return ShardingGateway::database(self::_DB_KEY)->update($query, self::_TABLE_KEY, $set, $dictionary_key, 1);
	}

	/**
	 * Конвертировать запись в структуру
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_PivotSystem_DefaultFile
	 */
	protected static function _convertRowToStruct(array $row):Struct_Db_PivotSystem_DefaultFile {

		return new Struct_Db_PivotSystem_DefaultFile(
			$row["dictionary_key"],
			$row["file_key"],
			$row["file_hash"],
			fromJson($row["extra"]),
		);
	}
}