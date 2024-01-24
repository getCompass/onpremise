<?php

namespace Compass\Thread;

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