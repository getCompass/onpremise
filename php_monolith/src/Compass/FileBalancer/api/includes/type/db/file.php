<?php

namespace Compass\FileBalancer;

/**
 * класс-интерфейс для работы с таблицами pivot_file_{year}.file_list_{month}|company_data.file_list
 */
class Type_Db_File {

	// функция для создания записи в таблице (здесь функция не принимает file_map, потому что он не может быть сформирован без meta_id
	// возвращаемая в этом методе)
	public static function insert(string $shard_id, int $table_id, array $insert):void {

		ShardingGateway::database(self::_getDbKey($shard_id))->insert(self::_getTableName($table_id), $insert);
	}

	// функция для получения записи по file_map
	public static function getOne(string $file_map):array {

		$shard_id = Type_Pack_File::getShardId($file_map);
		$table_id = Type_Pack_File::getTableId($file_map);
		$meta_id  = Type_Pack_File::getMetaId($file_map);

		$file_row = self::_tryGetOneByServer($shard_id, $table_id, $meta_id);
		if (!isset($file_row["meta_id"])) {
			throw new returnException("File not found in " . __METHOD__);
		}

		return self::_formatRow($file_row, $file_map);
	}

	// пробуем получить запись в зависимости от сервера
	protected static function _tryGetOneByServer(int $shard_id, int $table_id, int $meta_id):array {

		//
		if (defined("CURRENT_SERVER") && CURRENT_SERVER == PIVOT_SERVER) {

			// запрос проверен на EXPLAIN (INDEX=PRIMARY)
			$query = "SELECT * FROM `?p` WHERE `meta_id` = ?i LIMIT ?i";
			return ShardingGateway::database(self::_getDbKey($shard_id))->getOne($query, self::_getTableName($table_id), $meta_id, 1);
		}

		//
		if (defined("CURRENT_SERVER") && CURRENT_SERVER == CLOUD_SERVER) {

			// запрос проверен на EXPLAIN (INDEX=PRIMARY)
			$query = "SELECT * FROM `?p` WHERE `meta_id` = ?i AND `year` = ?i AND `month` = ?i LIMIT ?i";
			return ShardingGateway::database(self::_getDbKey($shard_id))->getOne($query, self::_getTableName($table_id), $meta_id, $shard_id, $table_id, 1);
		}

		throw new parseException("trying to get file db prefix from undefined server");
	}

	// получаем все записи
	public static function getAll(string $shard_id, int $table_id, array $meta_id_list):array {

		$file_list = self::_tryGetAllByServer($shard_id, $table_id, $meta_id_list);
		return self::_formatOutputFileList($file_list, $meta_id_list);
	}

	// пробуем получить все записи в зависимости от сервера
	protected static function _tryGetAllByServer(int $shard_id, int $table_id, array $meta_id_list):array {

		$db_key    = self::_getDbKey($shard_id);
		$table_key = self::_getTableName($table_id);

		//
		if (defined("CURRENT_SERVER") && CURRENT_SERVER == PIVOT_SERVER) {

			// запрос проверен на EXPLAIN (INDEX=PRIMARY)
			$query = "SELECT * FROM `?p` WHERE `meta_id` IN (?a) LIMIT ?i";
			return ShardingGateway::database($db_key)->getAll($query, $table_key, $meta_id_list, count($meta_id_list));
		}

		//
		if (defined("CURRENT_SERVER") && CURRENT_SERVER == CLOUD_SERVER) {

			// запрос проверен на EXPLAIN (INDEX=PRIMARY)
			$query = "SELECT * FROM `?p` WHERE `meta_id` IN (?a) AND `year` = ?i AND `month` = ?i LIMIT ?i";
			return ShardingGateway::database($db_key)->getAll($query, $table_key, $meta_id_list, $shard_id, $table_id, count($meta_id_list));
		}

		throw new parseException("trying to get file db prefix from undefined server");
	}

	// форматируем ответ из базы
	protected static function _formatOutputFileList(array $file_list, array $meta_id_list):array {

		$output = [];
		foreach ($meta_id_list as $k => $v) {

			foreach ($file_list as $item) {

				if ($v != $item["meta_id"]) {
					continue;
				}

				$output[] = self::_formatRow($item, $k);
			}
		}

		return $output;
	}

	// функция для получения записи по file_map
	public static function getForUpdate(string $file_map):array {

		$file_row = self::_tryGetForUpdateByServer($file_map);
		if (!isset($file_row["meta_id"])) {
			throw new returnException("File not found in " . __METHOD__);
		}

		return self::_formatRow($file_row, $file_map);
	}

	// функция для получения записи по file_map в зависимости от сервера
	protected static function _tryGetForUpdateByServer(string $file_map):array {

		$shard_id = Type_Pack_File::getShardId($file_map);
		$table_id = Type_Pack_File::getTableId($file_map);
		$meta_id  = Type_Pack_File::getMetaId($file_map);

		//
		if (defined("CURRENT_SERVER") && CURRENT_SERVER == PIVOT_SERVER) {

			// запрос проверен на EXPLAIN (INDEX=PRIMARY)
			$query = "SELECT * FROM `?p` WHERE `meta_id` = ?i LIMIT ?i FOR UPDATE";
			return ShardingGateway::database(self::_getDbKey($shard_id))->getOne($query, self::_getTableName($table_id), $meta_id, 1);
		}

		//
		if (defined("CURRENT_SERVER") && CURRENT_SERVER == CLOUD_SERVER) {

			// запрос проверен на EXPLAIN (INDEX=PRIMARY)
			$query = "SELECT * FROM `?p` WHERE `meta_id` = ?i AND `year` = ?i AND `month` = ?i LIMIT ?i FOR UPDATE";
			return ShardingGateway::database(self::_getDbKey($shard_id))->getOne($query, self::_getTableName($table_id), $meta_id, $shard_id, $table_id, 1);
		}

		throw new parseException("trying to get file db prefix from undefined server");
	}

	// функция для обновления записи по file_map
	public static function set(string $file_map, array $set):void {

		$shard_id = Type_Pack_File::getShardId($file_map);
		$table_id = Type_Pack_File::getTableId($file_map);
		$meta_id  = Type_Pack_File::getMetaId($file_map);

		//
		if (defined("CURRENT_SERVER") && CURRENT_SERVER == PIVOT_SERVER) {

			// запрос проверен на EXPLAIN (INDEX=PRIMARY)
			$query = "UPDATE `?p` SET ?u WHERE `meta_id` = ?i LIMIT ?i";
			ShardingGateway::database(self::_getDbKey($shard_id))->update($query, self::_getTableName($table_id), $set, $meta_id, 1);
			return;
		}

		//
		if (defined("CURRENT_SERVER") && CURRENT_SERVER == CLOUD_SERVER) {

			// запрос проверен на EXPLAIN (INDEX=PRIMARY)
			$query = "UPDATE `?p` SET ?u WHERE `meta_id` = ?i AND `year` = ?i AND `month` = ?i LIMIT ?i";
			ShardingGateway::database(self::_getDbKey($shard_id))->update($query, self::_getTableName($table_id), $set, $meta_id, $shard_id, $table_id, 1);
			return;
		}

		throw new parseException("trying to get file db prefix from undefined server");
	}

	// метод для старта транзакции
	public static function beginTransaction(string $file_map):bool {

		$shard_id = Type_Pack_File::getShardId($file_map);

		return ShardingGateway::database(self::_getDbKey($shard_id))->beginTransaction();
	}

	// метод для коммита транзакции
	public static function commit(string $file_map):bool {

		$shard_id = Type_Pack_File::getShardId($file_map);

		return ShardingGateway::database(self::_getDbKey($shard_id))->commit();
	}

	// метод для отката транзакции
	public static function rollback(string $file_map):bool {

		$shard_id = Type_Pack_File::getShardId($file_map);

		return ShardingGateway::database(self::_getDbKey($shard_id))->rollback();
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// функция для получения ключа базы данных
	protected static function _getDbKey(string $shard_id):string {

		return getFileDbName($shard_id);
	}

	// функция для получения названия таблицы
	protected static function _getTableName(int $table_id):string {

		return getFileTableName($table_id);
	}

	// функция для форматирования записи
	protected static function _formatRow(array $file_row, string $file_map):array {

		$file_row["file_map"] = $file_map;
		$file_row["extra"]    = fromJson($file_row["extra"]);

		unset($file_row["meta_id"]);

		if (isset($file_row["year"])) {
			unset($file_row["year"]);
		}

		if (isset($file_row["month"])) {
			unset($file_row["month"]);
		}

		if (is_null($file_row["content"])) {
			$file_row["content"] = "";
		}

		return $file_row;
	}
}