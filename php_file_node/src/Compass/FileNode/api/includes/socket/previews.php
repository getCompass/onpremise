<?php

namespace Compass\FileNode;

use BaseFrame\Exception\Request\ParamException;

/**
 * Группа методов для URL превью
 */
class Socket_Previews extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"doImageDownload",
	];

	/**
	 * загрузить изображение по ссылке
	 *
	 * @return array
	 * @throws ParamException
	 */
	public function doImageDownload() {

		$file_url    = $this->post("?s", "file_url");
		$company_id  = $this->post("?s", "company_id", 0);
		$company_url = $this->post("?s", "company_url", 0);

		// получаем название файла и если не нашли названия то отдаем ошибку
		$original_file_name = Type_File_Utils::getFileNameFromUrl($file_url);
		if (mb_strlen($original_file_name) < 1) {
			return $this->error(10020, "file download error");
		}

		// пробуем скачать файл
		try {
			$file_content = Helper_File::downloadFile($file_url);
		} catch (cs_DownloadFailed | \cs_CurlError) {
			return $this->error(10020, "file download error");
		}

		// сохраняем содержимое скачиваемого файла во временный файл
		$tmp_file_path = Type_File_Utils::generateTmpPath();
		Type_File_Utils::saveContentToFile($tmp_file_path, $file_content);

		// делаем сокет запрос для сохранения инфы о файле 
		try {
			$file_row = Helper_File::uploadFile($this->user_id, $company_id, $company_url, FILE_SOURCE_MESSAGE_PREVIEW_IMAGE, $original_file_name, $tmp_file_path);
		} catch (cs_InvalidFileTypeForSource) {
			return $this->error(10020, "file download error");
		}

		return $this->ok([
			"file_key" => (string) $file_row["file_key"],
		]);
	}
}