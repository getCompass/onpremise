<?php

namespace Compass\FileNode;

/**
 * Класс для обработки аудио
 */
class Type_File_Audio_Process {

	// делает первичную обработку аудио, возвращает extra
	public static function doProcessOnUpload(string $part_path, int $company_id, string $company_url, string $file_extension):array {

		// получаем file_path
		$file_path = Type_File_Utils::getFilePathFromPartPath($part_path);

		// получаем duration аудиофайла
		$duration = self::_getDuration($file_path);

		// проверим тип файла на конвертацию
		$is_need_convert = false;
		if (array_search($file_extension, Type_File_Audio_Main::EXTENSION_NEED_CONVERT_LIST)) {
			$is_need_convert = true;
		}

		return Type_File_Audio_Extra::getExtra($part_path, $company_id, $company_url, $duration, $is_need_convert);
	}

	// делает полную обработку картинки, возвращает extra
	public static function doPostProcess(string $part_path, int $company_id, array $extra, string $file_name):array {

		return self::_doProcess($part_path, $company_id, $extra, $file_name);
	}

	// -------------------------------------------------------
	// PROTECTED METHODS
	// -------------------------------------------------------

	// делает обработку картинку в зависимости от переданных параметров
	protected static function _doProcess(string $part_path, int $company_id, array $extra = [], string $file_name = ""):array {

		// получаем полный путь к аудио в файловой системе
		$file_path = Type_File_Utils::getFilePathFromPartPath($part_path);

		$file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
		$new_path       = str_replace($file_extension, $extra["convert_to"], $file_path);
		$new_part_path  = str_replace($file_extension, $extra["convert_to"], $part_path);
		$is_success     = ffmpeg_exec("-y",
			"-i", $file_path, // входной файл
			"-f", Type_File_Audio_Main::CONVERT_TYPE,      // тип в который необходимо сконвертировать
			"-ab",
			"192000",         // битрейт 192кбит в секунду
			"-vn",            // отключим видео(мало ли так произойдет)
			"+faststart",     // перемещаем флаг moov_atom в начало
			$new_path         // выходной файл
		);

		// установим статус после конвертации
		if ($is_success) {
			$extra["status"] = Type_File_Main::STATUS_OK;
		} else {
			$extra["status"] = Type_File_Main::STATUS_ERROR;
		}

		$extra["name_convert_file"] = str_replace($file_extension, $extra["convert_to"], $file_name);
		$extra["convert_part_path"] = $new_part_path;
		$extra["company_id"]        = $company_id;

		return $extra;
	}

	// функция для получения продолжительности аудио-файла
	protected static function _getDuration(string $file_path):int {

		// получаем длину аудио
		return Type_Extension_File::getAudioDuration($file_path);
	}
}