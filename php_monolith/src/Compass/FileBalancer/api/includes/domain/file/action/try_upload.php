<?php

namespace Compass\FileBalancer;

/**
 * Действия загрузки файла.
 */
class Domain_File_Action_TryUpload {

	/**
	 * Выполняет сохранение данных о загруженном файле.
	 */
	public static function run(
		int    $user_id, int $file_type,
		int    $file_source, int $node_id, int $size_kb, int $created_at,
		string $mime_type, string $file_name, string $file_extension,
		array  $extra, string $file_hash, int $is_cdn, int $status
	):array {

		$shard_id = Type_Pack_File::getShardIdByTime($created_at);
		$table_id = Type_Pack_File::getTableIdByTime($created_at);

		// сохраняем запись о файле в базу
		$file_row = static::_makeFileRow(
			$user_id, $shard_id, $table_id, $file_type, $file_source, $node_id, $size_kb,
			$created_at, $mime_type, $file_name, $file_extension, $extra, $file_hash, $is_cdn, $status
		);

		$meta_id = $file_row["meta_id"];
		Type_File_Main::insert($shard_id, $table_id, $file_row);

		$file_size_list = static::_getFileSizeList($file_type, $extra);

		$file_row["file_map"] = Type_Pack_File::doPack(
			$shard_id, $table_id, $meta_id, $created_at, $file_type, $file_source, $file_size_list["width"], $file_size_list["height"]
		);

		$node_url = Type_Node_Config::getNodeUrl($node_id);

		// добавляем в поиск, если этот файл может быть найден
		if (static::_isSearchable($file_row["file_map"])) {
			static::_indexFileData($file_row["file_map"]);
		}

		return [$file_row, $node_url, Type_File_Utils::makeDownloadToken($file_row["extra"]["original_part_path"])];
	}

	/**
	 * Формирует рабочие данные загруженного данные файла.
	 */
	protected static function _makeFileRow(
		int    $user_id, int $shard_id, int $table_id, int $file_type,
		int    $file_source, int $node_id, int $size_kb, int $created_at,
		string $mime_type, string $file_name, string $file_extension,
		array  $extra, string $file_hash, int $is_cdn, int $status,
	):array {

		if (!defined("CURRENT_SERVER")) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("undefined server");
		}

		$meta_id = match (CURRENT_SERVER) {

			PIVOT_SERVER => Type_Autoincrement_Pivot::getNextId(Type_Autoincrement_Pivot::getFileMetaIdKey($shard_id, $table_id)),
			CLOUD_SERVER => Type_Autoincrement_Company::getNextId(Type_Autoincrement_Company::FILE_META_ID),
			default      => throw new \BaseFrame\Exception\Domain\ParseFatalException("undefined server"),
		};

		$file_row = [
			"meta_id"        => $meta_id,
			"file_type"      => $file_type,
			"file_source"    => $file_source,
			"is_deleted"     => 0,
			"is_cdn"         => $is_cdn,
			"status"         => $status,
			"node_id"        => $node_id,
			"created_at"     => $created_at,
			"updated_at"     => 0,
			"size_kb"        => $size_kb,
			"user_id"        => $user_id,
			"file_hash"      => $file_hash,
			"mime_type"      => $mime_type,
			"file_name"      => $file_name,
			"file_extension" => $file_extension,
			"extra"          => $extra,
			"content"        => "",
		];

		if (CURRENT_SERVER === CLOUD_SERVER) {

			$file_row["year"]  = $shard_id;
			$file_row["month"] = $table_id;
		}

		return $file_row;
	}

	/**
	 * Возвращает ширину и высоту переданного видео/картинки
	 */
	protected static function _getFileSizeList(int $file_type, array $extra):array {

		// если тип файла - изображение или видео
		$output["width"]  = 0;
		$output["height"] = 0;

		switch ($file_type) {

			case FILE_TYPE_IMAGE:

				// получаем размеры оригинального изображения
				$output["width"]  = $extra["original_image_item"]["width"];
				$output["height"] = $extra["original_image_item"]["height"];

				break;

			case FILE_TYPE_VIDEO:

				// получаем размеры оригинального видео
				$output["width"]  = $extra["original_video_item"]["width"];
				$output["height"] = $extra["original_video_item"]["height"];

				break;
		}

		return $output;
	}

	/**
	 * Проверяет, является ли файл файлом пространства
	 */
	protected static function _isSearchable(string $file_map):bool {

		return Type_Pack_File::getCompanyId($file_map) !== 0;
	}

	/**
	 * Запускает обновление поисковых данных файла.
	 */
	protected static function _indexFileData(string $file_map):void {

		try {

			// пытаемся зарядить задачу на индексацию файла
			Gateway_Socket_Conversation::indexFile($file_map);
		} catch (\Exception $e) {
			// если не удалось передать на индексацию, то ничего страшного
		}
	}
}