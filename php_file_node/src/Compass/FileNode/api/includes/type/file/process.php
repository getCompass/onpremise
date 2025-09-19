<?php

namespace Compass\FileNode;

/**
 * Модель для обработки файлов
 */
class Type_File_Process {

	// метод для обработки файла
	// @long - switch
	public static function doProcessOnUpload(string $part_path, int $company_id, string $company_url, int $file_type, int $user_id, string $parent_file_key = "", string $file_extension = ""):array {

		// делаем первичную обработку в зависимости от типа файла
		switch ($file_type) {

			case FILE_TYPE_DEFAULT:

				$extra = Type_File_Default_Process::doProcessOnUpload($part_path, $company_id, $company_url);
				break;
			case FILE_TYPE_IMAGE:

				$extra = Type_File_Image_Process::doProcessOnUpload($part_path, $company_id, $company_url, $parent_file_key);
				break;
			case FILE_TYPE_VIDEO:

				$extra = Type_File_Video_Process::doProcessOnUpload($part_path, $company_id, $company_url);
				break;
			case FILE_TYPE_AUDIO:

				$extra = Type_File_Audio_Process::doProcessOnUpload($part_path, $company_id, $company_url, $file_extension);
				break;
			case FILE_TYPE_DOCUMENT:

				$extra = Type_File_Document_Process::doProcessOnUpload($part_path, $company_id, $company_url);
				break;
			case FILE_TYPE_ARCHIVE:

				$extra = Type_File_Archive_Process::doProcessOnUpload($part_path, $company_id, $company_url);
				break;
			case FILE_TYPE_VOICE:

				$extra = Type_File_Voice_Process::doProcessOnUpload($part_path, $user_id, $company_id, $company_url);
				break;
			default:
				throw new \ParseException("Unhandled file_type [{$file_type}] IN " . __METHOD__);
		}

		return $extra;
	}

	// отправляем на пост обработку если надо
	public static function sendToPostUpload(array $file_row, string $part_path, int $need_work = 0):void {

		if ($need_work === 0) {
			$need_work = time();
		}

		// если для файла необходимо выполнить post upload process
		switch ($file_row["file_type"]) {

			case FILE_TYPE_IMAGE:

				self::_addImageToPostProcessQueue($file_row["file_key"], $file_row["file_type"], $part_path, $need_work);
				break;

			case FILE_TYPE_VIDEO:

				self::_addVideoToPostProcessQueue(
					$file_row["file_key"], $file_row["file_type"], $part_path, $file_row["extra"]["video_version_list"], $file_row["extra"]["duration"], $need_work
				);
				break;

			case FILE_TYPE_AUDIO:

				self::_addAudioToPostProcessQueue($file_row["file_key"], $file_row["file_type"], $part_path, $file_row["extra"], $need_work);
				break;

			case FILE_TYPE_DOCUMENT:

				self::_addDocumentToPostProcessQueue($file_row["file_key"], $file_row["file_type"], $file_row["file_extension"], $part_path, $file_row["extra"], $need_work);
				break;

			default:
				break;
		}
	}

	// добавляем в очередь на пост обработку изображение
	protected static function _addImageToPostProcessQueue(string $file_key, int $file_type, string $part_path, int $need_work):void {

		// вставляем записи
		$insert = [
			"file_key"    => $file_key,
			"file_type"   => $file_type,
			"error_count" => 0,
			"need_work"   => $need_work,
			"part_path"   => $part_path,
			"extra"       => [],
		];

		// добавляем в очередь
		Gateway_Db_FileNode_PostUpload::insert($insert);
	}

	// добавляем в очередь на пост обработку АУДИО
	protected static function _addAudioToPostProcessQueue(string $file_key, int $file_type, string $part_path, array $extra, int $need_work):void {

		// если нет нужды конвертировать
		if ($extra["status"] == Type_File_Main::CONVERT_STATUS_OK) {
			return;
		}

		// формируем массив по аудио - в какой тип необходимо преобразовать
		$insert = [
			"queue_id"    => null,
			"file_key"    => $file_key,
			"file_type"   => $file_type,
			"error_count" => 0,
			"need_work"   => $need_work,
			"part_path"   => $part_path,
			"extra"       => $extra,
		];

		// добавляем в очередь
		Gateway_Db_FileNode_PostUpload::insert($insert);
	}

	// добавляем в очередь на пост обработку ВИДЕО
	protected static function _addVideoToPostProcessQueue(string $file_key, int $file_type, string $part_path, array $video_version_list, int $duration, int $need_work):void {

		// если нет версий на которые надо нарезать
		if (count($video_version_list) < 1) {
			return;
		}

		// формируем массив размеров видео - которые нужно нарезать на постобработке
		$insert = [];
		foreach ($video_version_list as $v) {

			// засекаем время начала постобработки
			$v["postupload_start_at"] = $need_work;
			$v["duration"]            = $duration;

			$insert[] = [
				"queue_id"    => null,
				"file_key"    => $file_key,
				"file_type"   => $file_type,
				"error_count" => 0,
				"need_work"   => $need_work,
				"part_path"   => $part_path,
				"extra"       => $v,
			];
		}

		// добавляем в очередь
		Gateway_Db_FileNode_PostUpload::insertArray($insert);
	}

	/**
	 * Добавляем текстовый документ на пост обработку
	 */
	protected static function _addDocumentToPostProcessQueue(string $file_key, int $file_type, string $file_extension, string $part_path, array $extra, int $need_work):void {

		// если нет нужды конвертировать
		if (!Type_File_Document_Main::isIndexableDocument($file_extension)) {
			return;
		}

		// формируем запись в очередь на обработку
		$insert = [
			"queue_id"    => null,
			"file_key"    => $file_key,
			"file_type"   => $file_type,
			"error_count" => 0,
			"need_work"   => $need_work,
			"part_path"   => $part_path,
			"extra"       => $extra,
		];

		// добавляем в очередь
		Gateway_Db_FileNode_PostUpload::insert($insert);
	}
}