<?php

namespace Compass\FileBalancer;

/**
 * обертка для работы с моделями файлов
 */
class Type_File_Main {

	// список доступных file_source
	public const ALLOWED_FILE_SOURCE_LIST = [
		FILE_SOURCE_AVATAR,
		FILE_SOURCE_MESSAGE_DEFAULT,
		FILE_SOURCE_MESSAGE_IMAGE,
		FILE_SOURCE_MESSAGE_VIDEO,
		FILE_SOURCE_MESSAGE_AUDIO,
		FILE_SOURCE_MESSAGE_DOCUMENT,
		FILE_SOURCE_MESSAGE_ARCHIVE,
		FILE_SOURCE_MESSAGE_VOICE,
		FILE_SOURCE_MESSAGE_PREVIEW_IMAGE,
		FILE_SOURCE_MESSAGE_ANY,
		FILE_SOURCE_AVATAR_DEFAULT,
	];

	// список доступных file_source для CDN
	public const ALLOWED_FILE_SOURCE_CDN_LIST = [
		FILE_SOURCE_AVATAR_CDN,
		FILE_SOURCE_VIDEO_CDN,
		FILE_SOURCE_DOCUMENT_CDN,
	];

	// получаем одну запись из базы
	public static function getOne(string $file_map):array {

		return Type_Db_File::getOne($file_map);
	}

	// получаем все записи
	public static function getAll(array $file_map_list):array {

		// группируем список файлов по таблицам
		$grouped_file_list = self::_doGroupFileMapListByTables($file_map_list);

		// получаем список файлов из базы
		return self::_getFileList($grouped_file_list);
	}

	// группируем массив файлов по таблицам
	protected static function _doGroupFileMapListByTables(array $file_map_list):array {

		$grouped_file_list = [];
		foreach ($file_map_list as $item) {

			$full_table_name = self::_getFullTableNameFromFileMap($item);
			$meta_id         = Type_Pack_File::getMetaId($item);

			$grouped_file_list[$full_table_name][$item] = $meta_id;
		}

		return $grouped_file_list;
	}

	// получаем ключ для группировки file_map_list
	protected static function _getFullTableNameFromFileMap(string $file_map):string {

		$shard_id    = Type_Pack_File::getShardId($file_map);
		$table_id    = Type_Pack_File::getTableId($file_map);
		$server_type = Type_Pack_File::getServerType($file_map);
		return "{$shard_id}.{$table_id}.{$server_type}";
	}

	// получаем записи из базы
	protected static function _getFileList(array $grouped_file_list):array {

		$output = [];
		foreach ($grouped_file_list as $k => $v) {

			$keys      = explode(".", $k);
			$file_list = Type_Db_File::getAll($keys[0], $keys[1], $v);

			foreach ($file_list as $item) {
				$output[] = $item;
			}
		}

		return $output;
	}

	// функция для создания записи в таблице
	public static function insert(string $shard_id, int $table_id, array $insert):void {

		Type_Db_File::insert($shard_id, $table_id, $insert);
	}

	// функция для обновления записи в таблице
	public static function set(string $file_map, array $set):void {

		Type_Db_File::set($file_map, $set);
	}
}