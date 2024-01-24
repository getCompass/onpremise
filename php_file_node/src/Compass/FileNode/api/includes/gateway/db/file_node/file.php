<?php

namespace Compass\FileNode;

/**
 * класс-интерфейс для таблицы file_node.file
 */
class Gateway_Db_FileNode_File extends Gateway_Db_FileNode_Main {

	protected const _TABLE_KEY = "file";

	// получить запись по первичному ключу
	public static function getOne(string $file_key):array {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$file_row = \sharding::pdo(self::_getDbKey())->getOne("SELECT * FROM `?p` WHERE file_key = ?s LIMIT ?i", self::_TABLE_KEY, $file_key, 1);

		if (isset($file_row["file_key"])) {
			$file_row["extra"] = fromJson($file_row["extra"]);
		}

		return $file_row;
	}

	// взять запись с блокировкой
	public static function getForUpdate(string $file_key):array {

		$file_row = \sharding::pdo(self::_getDbKey())->getOne("SELECT * FROM `?p` WHERE file_key = ?s LIMIT ?i FOR UPDATE", self::_TABLE_KEY, $file_key, 1);

		if (isset($file_row["file_key"])) {
			$file_row["extra"] = fromJson($file_row["extra"]);
		}

		return $file_row;
	}

	// создаем запись в таблице
	public static function insert(array $insert):void {

		\sharding::pdo(self::_getDbKey())->insert(self::_TABLE_KEY, $insert);
	}

	// обновляем имеющуюся запись
	public static function update(string $file_key, array $set):void {

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query = "UPDATE `?p` SET ?u WHERE file_key = ?s LIMIT ?i";
		\sharding::pdo(self::_getDbKey())->update($query, self::_TABLE_KEY, $set, $file_key, 1);
	}

	// метод для получения списка файлов
	public static function getList(int $expire, int $limit, int $offset):array {

		// запрос проверен на EXPLAIN (INDEX=is_deleted,last_access_at)
		$query = "SELECT * FROM `?p` WHERE is_deleted = ?i AND  `last_access_at` < ?i LIMIT ?i OFFSET ?i";
		return \sharding::pdo(self::_getDbKey())->getAll($query, "file", 0, $expire, $limit, $offset);
	}
}