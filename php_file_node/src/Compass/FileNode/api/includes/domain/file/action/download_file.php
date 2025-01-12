<?php

namespace Compass\FileNode;

/** класс описывает действие скачивания файла по ссылке и его сохранения на ноде */
class Domain_File_Action_DownloadFile {

	/**
	 * выполняем действие
	 *
	 * @return array
	 * @throws cs_DownloadFailed
	 * @throws cs_InvalidFileTypeForSource
	 */
	public static function do(int $user_id, string $file_url, int $company_id, string $company_url, int $file_source):array {

		// получаем название файла и если не нашли названия то отдаем ошибку
		$original_file_name = Type_File_Utils::getFileNameFromUrl($file_url);

		if (mb_strlen($original_file_name) < 1) {
			throw new Domain_File_Exception_IncorrectFileName();
		}

		// пробуем скачать файл
		$file_content = Helper_File::downloadFile($file_url);

		// сохраняем содержимое скачиваемого файла во временный файл
		$tmp_file_path = Type_File_Utils::generateTmpPath();
		Type_File_Utils::saveContentToTmp($tmp_file_path, $file_content);

		// сохраняем файл
		[$file_row] = Helper_File::uploadFile($user_id, $company_id, $company_url, $file_source, $original_file_name, $tmp_file_path);
		return $file_row;
	}
}