<?php

namespace Compass\FileNode;

use BaseFrame\Server\ServerProvider;

/**
 * хелпер для работы с файлами
 */
class Helper_File {

	protected const _REDIRECT_MAX_COUNT               = 10;                // максимальное количество редиректов
	public const    MAX_IMAGE_DOWNLOAD_CONTENT_LENGTH = 20 * 1024 * 1024;  // максимальный размер содержимого для превью
	public const    MAX_FILE_DOWNLOAD_CONTENT_LENGTH  = 50 * 1024 * 1024;  // максимальный размер содержимого для файлов

	// скачивание файла
	public static function downloadFile(string $file_url, bool $is_source_trusted = false, int $timeout = 2, string|bool $node_id = false, int $max_file_size = self::MAX_IMAGE_DOWNLOAD_CONTENT_LENGTH):string {

		// если файл находится на этой же ноде
		if (NODE_ID == $node_id) {

			$path = parse_url($file_url)["path"];
			$path = strstr($path, FOLDER_FILE_NAME);

			return file_get_contents(PATH_WWW . $path);
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

			// если размер контента больше 20mb - выходим
			if ($headers["content-length"] > $max_file_size) {
				throw new cs_DownloadFailed();
			}
		}

		// скачиваем файл
		$file_url = $curl->getEffectiveUrl();
		$curl->setOpt(CURLOPT_NOBODY, false);
		$curl->setOpt(CURLOPT_FOLLOWLOCATION, true);

		// мониторим, сколько скачали, если больше 20 мб, то завершаем
		// нельзя верить только хедеру content-length. Злоумышленник или кривые руки разраба может им манипулировать. Для этого и добавлена функция прогресса
		$curl->setOpt(CURLOPT_MAXFILESIZE, $max_file_size);
		$curl->setOpt(CURLOPT_PROGRESSFUNCTION, function(int $download_size, int $downloaded, int $max_file_size) {

			return ($downloaded > $max_file_size) ? 1 : 0;
		});

		try {
			$content = $curl->getImage($file_url);
		} catch (\cs_CurlError) {

			// ошибка курла возникнет, если вышли за пределы загружаемого или поймали таймаут
			throw new cs_DownloadFailed();
		}

		if ($curl->getResponseCode() != 200) {
			throw new cs_DownloadFailed();
		}
		return $content;
	}

	// устанавливаем timeout для курла
	protected static function _setTimeout(\Curl $curl, int $timeout):void {

		$curl->setTimeout($timeout);

		if (ServerProvider::isTest()) {
			$curl->setTimeout(20);
		}
	}

	// метод для сохранения файла на ноде
	public static function uploadFile(int $user_id, int $company_id, string $company_url, int $file_source, string $original_file_name, string $tmp_file_path, string $parent_file_key = "", bool $is_cdn = false):array {

		$mime_type      = Type_File_Utils::getMimeType($tmp_file_path);
		$file_extension = Type_File_Utils::getExtension($original_file_name);
		$size_kb        = Type_File_Utils::getFileSizeKb($tmp_file_path);
		$file_hash      = Type_File_Utils::getFileHash($tmp_file_path);
		$file_type      = Type_File_Main::getFileType($tmp_file_path, $mime_type, $size_kb, $file_source, $file_extension);
		$file_source    = Type_File_Main::tryGetFileSource($file_type, $file_source);
		$part_path      = Type_File_Main::moveFileToRandomDir($tmp_file_path, $file_extension, $company_id);
		$file_path      = Type_File_Utils::getFilePathFromPartPath($part_path);

		// если тип определилили не как картинку, а файл должен им быть - возвращаем ошибку
		if ($file_type !== FILE_TYPE_IMAGE && $file_source === FILE_SOURCE_MESSAGE_PREVIEW_IMAGE) {
			throw new cs_InvalidFileTypeForSource();
		}

		// делаем первичную обработку в зависимости от типа файла
		try {
			$extra = Type_File_Process::doProcessOnUpload($part_path, $company_id, $company_url, $file_type, $user_id, $parent_file_key, $file_extension);
		} catch (cs_FileProcessFailed | cs_VideoProcessFailed) {

			// если не смогли обработать и это превью то выбрасываем exception
			if ($file_source == FILE_SOURCE_MESSAGE_PREVIEW_IMAGE) {
				throw new cs_InvalidFileTypeForSource();
			}

			// иначе отдаем как дефолт и обрабтываем его так
			$file_type = FILE_TYPE_DEFAULT;
			$extra     = Type_File_Default_Process::doProcessOnUpload($part_path, $company_id, $company_url);
		}

		// получаем размер изображения после обработки
		$size_kb = Type_File_Utils::getFileSizeKb($file_path);

		// сохраняем файл на файловой ноде
		$file_row = Type_File_Main::create(
			$user_id, $file_type, $file_source, $size_kb, $mime_type, $original_file_name,
			$file_extension, $extra, $part_path, $file_hash, $is_cdn
		);

		// оправляем на пост обработку
		Type_File_Process::sendToPostUpload($file_row, $part_path);

		return $file_row;
	}

}