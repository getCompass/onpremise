<?php

namespace Compass\Conversation;

/**
 * класс-интерфейс для таблицы preview_list в cloud_preview
 */
class Gateway_Db_CompanyData_PreviewList extends Gateway_Db_CompanyData_Main {

	protected const _TABLE_KEY = "preview_list";

	// -------------------------------------------------------
	// PUBLIC METHODS
	// -------------------------------------------------------

	// метод для создания записи
	public static function insert(array $insert):void {

		static::_connect()->insert(self::_getTable(), $insert);
	}

	// метод для получения записи
	public static function getOne(string $preview_map):array {

		// получаем preview_hash в качестве идентификатора
		$preview_hash = \CompassApp\Pack\Preview::getPreviewHash($preview_map);

		$preview_row = static::_connect()->getOne("SELECT * FROM `?p` WHERE preview_hash = ?s LIMIT ?i", self::_getTable(), $preview_hash, 1);

		if (isset($preview_row["preview_hash"])) {

			$preview_row["data"]        = fromJson($preview_row["data"]);
			$preview_row["preview_map"] = $preview_map;

			unset($preview_row["preview_hash"]);
		}

		return $preview_row;
	}

	// получаем все записи
	public static function getAll(array $preview_map_list):array {

		$preview_hash_list = [];
		foreach ($preview_map_list as $preview_map) {
			$preview_hash_list[$preview_map] = \CompassApp\Pack\Preview::getPreviewHash($preview_map);
		}

		// запрос проверен на EXPLAIN (INDEX=PRIMARY)
		$query        = "SELECT * FROM `?p` WHERE preview_hash IN (?a) LIMIT ?i";
		$preview_list = static::_connect()->getAll($query, self::_getTable(), array_values($preview_hash_list), count($preview_hash_list));
		return self::_formatOutputPreviewList($preview_list, $preview_hash_list);
	}

	// форматируем ответ из базы
	protected static function _formatOutputPreviewList(array $preview_list, array $preview_hash_list):array {

		$output = [];
		foreach ($preview_hash_list as $k => $v) {

			foreach ($preview_list as $item) {

				if ($v != $item["preview_hash"]) {
					continue;
				}

				$item["data"]        = fromJson($item["data"]);
				$item["preview_map"] = $k;
				unset($item["preview_hash"]);

				$output[] = $item;
			}
		}

		return $output;
	}

	// метод для обновления записи
	public static function set(string $preview_map, array $set):void {

		// получаем preview_hash в качестве идентификатора
		$preview_hash = \CompassApp\Pack\Preview::getPreviewHash($preview_map);

		static::_connect()->update("UPDATE `?p` SET ?u WHERE preview_hash = ?s LIMIT ?i",
			self::_getTable(), $set, $preview_hash, 1);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// получаем таблицу
	protected static function _getTable():string {

		return self::_TABLE_KEY;
	}
}