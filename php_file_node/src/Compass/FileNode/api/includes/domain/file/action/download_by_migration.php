<?php

namespace Compass\FileNode;

use BaseFrame\System\File;

/**
 * Класс описывает действие скачивания файла по ссылке и его сохранения на ноде через миграцию
 */
class Domain_File_Action_DownloadByMigration {

	protected const    _REDIRECT_MAX_COUNT               = 10;                        // максимальное количество редиректов
	protected const    _MAX_FILE_DOWNLOAD_CONTENT_LENGTH = 1024 * 1024 * 1024;      // максимальный размер содержимого для файлов

	/**
	 * Выполняем действие
	 *
	 * @throws cs_InvalidFileTypeForSource
	 * @throws \cs_CurlError
	 * @throws cs_DownloadFailed
	 */
	public static function do(int $user_id, string $file_url, int $company_id, string $company_url, int $file_source, string $original_file_name):array {

		if (mb_strlen($original_file_name) < 1) {
			throw new Domain_File_Exception_IncorrectFileName();
		}

		// пробуем скачать файл
		$file_content = self::_downloadFile($file_url);

		// сохраняем содержимое скачиваемого файла во временный файл
		$tmp_file_path = Type_File_Utils::generateTmpPath();
		Type_File_Utils::saveContentToTmp($tmp_file_path, $file_content);

		if ($file_source === FILE_SOURCE_MESSAGE_VOICE) {

			ffmpeg_exec("-i", $tmp_file_path, "-map", "a", $new_tmp_file_path = Type_File_Utils::generateTmpPath("aac"));
			$original_file_name = pathinfo($original_file_name, PATHINFO_FILENAME) . ".aac";
			unlink($tmp_file_path); // nosemgrep
			$tmp_file_path = $new_tmp_file_path;
		}

		Type_System_Admin::log("migration-file-upload", "user_id: {$user_id} company_urk: {$company_url} file_url: {$file_url} file_source: {$file_source} original_file_name: {$original_file_name} tmp_file_path: {$tmp_file_path}");

		// сохраняем файл
		[$file_row] = Helper_File::uploadFile($user_id, $company_id, $company_url, $file_source, $original_file_name, $tmp_file_path);
		return $file_row;
	}

	/**
	 * Скачивание файла
	 *
	 * @throws \cs_CurlError
	 * @throws cs_DownloadFailed
	 */
	protected static function _downloadFile(string $file_url, bool $is_source_trusted = false, int $timeout = 120, string|bool $node_id = false, int $max_file_size = self::_MAX_FILE_DOWNLOAD_CONTENT_LENGTH):string {

		// если файл находится на этой же ноде
		if (NODE_ID == $node_id) {

			$path = parse_url($file_url)["path"];
			$path = strstr($path, FOLDER_FILE_NAME);

			return File::init(PATH_WWW, $path)->read();
		}

		$curl = new \Curl();
		self::_setTimeout($curl, $timeout);

		// получаем файл
		$curl->setOpt(CURLOPT_NOBODY, true);
		$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
		$curl->setOpt(CURLOPT_MAXREDIRS, self::_REDIRECT_MAX_COUNT);

		$curl->get($file_url);

		if ($curl->getResponseCode() != 200) {
			throw new cs_DownloadFailed();
		}

		// если нам отдали хедер content-length, то на берегу проверяем, что мы можем скачать файл такой длины
		$headers = $curl->getHeaders();
		$headers = array_change_key_case($headers, CASE_LOWER);
		if (!$is_source_trusted && isset($headers["content-length"])) {

			// если нам отдали массив из длины контента
			if (is_array($headers["content-length"])) {
				$headers["content-length"] = array_pop($headers["content-length"]);
			}

			// если размер контента больше 1000mb - выходим
			if ($headers["content-length"] > $max_file_size) {
				throw new cs_DownloadFailed();
			}
		}

		// скачиваем файл
		$file_url = $curl->getEffectiveUrl();
		$curl->setOpt(CURLOPT_NOBODY, false);
		$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);
		// мониторим, сколько скачали, если больше 1000 мб, то завершаем
		// нельзя верить только хедеру content-length. Злоумышленник или кривые руки разраба может им манипулировать. Для этого и добавлена функция прогресса
		$curl->setOpt(CURLOPT_MAXFILESIZE, $max_file_size);
		$curl->setOpt(CURLOPT_PROGRESSFUNCTION, function(int $download_size, int $downloaded, int $max_file_size) {

			return ($downloaded > $max_file_size) ? 1 : 0;
		});

		try {
			$content = $curl->get($file_url);
		} catch (\cs_CurlError) {

			// ошибка курла возникнет, если вышли за пределы загружаемого или поймали таймаут
			throw new cs_DownloadFailed();
		}

		if ($curl->getResponseCode() != 200) {
			throw new cs_DownloadFailed();
		}
		return $content;
	}

	/**
	 * Устанавливаем timeout для curl
	 */
	protected static function _setTimeout(\Curl $curl, int $timeout):void {

		$curl->setTimeout($timeout);
	}
}