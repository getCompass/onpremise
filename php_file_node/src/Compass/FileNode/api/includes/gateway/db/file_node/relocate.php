<?php

namespace Compass\FileNode;

/**
 * Класс для работы с таблицей relocate_queue
 */
class Gateway_Db_FileNode_Relocate extends Gateway_Db_FileNode_Main {

	protected const _TABLE_KEY = "relocate_queue";

	// метод для добавляение новой записи в таблицу
	public static function insert(string $file_key):void {

		\sharding::pdo(self::_getDbKey())->insert(self::_TABLE_KEY, [
			"file_key"    => $file_key,
			"error_count" => 0,
			"need_work"   => 0,
		]);
	}
}