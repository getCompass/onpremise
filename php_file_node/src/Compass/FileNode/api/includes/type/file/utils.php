<?php

namespace Compass\FileNode;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\System\File;
use JetBrains\PhpStorm\ArrayShape;
use JetBrains\PhpStorm\Pure;

/**
 * Вспомогательные функции
 */
class Type_File_Utils {

	// подготавливает файл к передаче в Apiv1_Format
	// @long --- switch ... case
	public static function prepareFileForFormat(array $file_row, int $user_id, string $download_token = ""):array {

		$data = [];

		// если есть родительский файл от файла
		if (isset($file_row["extra"]["parent_file_key"])) {
			$data["parent"]["file_key"] = $file_row["extra"]["parent_file_key"];
		}

		// выбираем тип файла и формируем его дату
		switch ($file_row["file_type"]) {

			case FILE_TYPE_IMAGE:

				// добавляем оригинальное изображение в image_size
				$file_row["extra"]["image_size_list"][] = $file_row["extra"]["original_image_item"];
				$data["image_version_list"]             = self::_getImageData(NODE_URL, $file_row["extra"]["image_size_list"], $download_token);
				break;
			case FILE_TYPE_AUDIO:

				$data["duration"]           = $file_row["extra"]["duration"];
				$data["original_part_path"] = $file_row["extra"]["original_part_path"];
				$data["status"]             = $file_row["extra"]["status"];
				break;
			case FILE_TYPE_VOICE:

				$data["duration"]    = $file_row["extra"]["duration"];
				$data["duration_ms"] = Type_File_Voice_Extra::getDurationInMs($file_row["extra"]);
				$data["waveform"]    = $file_row["extra"]["waveform"];
				$data["is_listen"]   = Type_File_Voice_Extra::isListenByUser($file_row["extra"], $user_id);
				break;
			case FILE_TYPE_VIDEO:

				$data = self::_getVideoData(NODE_URL, $file_row["extra"], $download_token);
		}

		$url = static::_getDownloadUrlByPartPath(NODE_URL, $file_row["extra"]["original_part_path"], $download_token);

		return [
			"file_key"       => $file_row["file_key"],
			"size_kb"        => $file_row["size_kb"],
			"created_at"     => $file_row["created_at"],
			"type"           => $file_row["file_type"],
			"url"            => $url,
			"file_name"      => $file_row["file_name"],
			"file_extension" => $file_row["file_extension"],
			"file_hash"      => $file_row["file_hash"],
			"data"           => $data,
			"node_url"       => NODE_URL,
		];
	}

	// формируем image дату
	protected static function _getImageData(string $node_url, array $image_version_list, string $download_token):array {

		// проходимся по всему массиву и приводим формат
		foreach ($image_version_list as $k => $v) {
			$image_version_list[$k] = self::_getImageItem($node_url, $v, $download_token);
		}

		return $image_version_list;
	}

	// формируем image item
	#[ArrayShape(["url" => "string", "width" => "mixed", "height" => "mixed", "size_kb" => "mixed"])]
	protected static function _getImageItem(string $node_url, array $image_version_item, string $download_token):array {

		return [
			"url"     => self::_getDownloadUrlByPartPath($node_url, $image_version_item["part_path"], $download_token),
			"width"   => $image_version_item["width"],
			"height"  => $image_version_item["height"],
			"size_kb" => $image_version_item["size_kb"],
		];
	}

	// получаем видео дату
	#[ArrayShape(["original_video_info" => "mixed", "image_version_list" => "array", "duration" => "mixed", "preview" => "string", "video_version_list" => "array"])]
	protected static function _getVideoData(string $node_url, array $extra, string $download_token):array {

		return [
			"original_video_info" => $extra["original_video_item"],
			"image_version_list"  => self::_getImageData($node_url, $extra["preview_size_list"], $download_token),
			"duration"            => $extra["duration"],
			"preview"             => self::_getDownloadUrlByPartPath($node_url, $extra["preview_original_part_path"], $download_token),
			"video_version_list"  => self::_getVideoVersionList($node_url, $extra["video_version_list"]),
		];
	}

	/**
	 * Получить video_version_list в файлах-видео
	 *
	 * @param string $node_url
	 * @param array  $video_version_list
	 *
	 * @return array
	 */
	protected static function _getVideoVersionList(string $node_url, array $video_version_list):array {

		$output = [];

		// проходимся по всему массиву и приводим формат
		foreach ($video_version_list as $k => $v) {

			// ключ выглядит как 720_1, нужна первая часть
			$video_quality = (int) explode("_", $k)[0];
			$file_url      = self::_getUrlByPartPath($node_url, $v["part_path"]);
			$output[]      = self::_getVideoVersionItem($v, $file_url, $video_quality);
		}

		return $output;
	}

	/**
	 * Получаем один элемент video_version_list
	 *
	 * @param array  $video_version_item
	 * @param string $file_url
	 * @param int    $video_quality
	 *
	 * @long - switch...case
	 * @return array
	 * @throws \ParseException
	 */
	protected static function _getVideoVersionItem(array $video_version_item, string $file_url, int $video_quality):array {

		// switch по типу видео - получаем название формата
		$format = match ($video_version_item["video_type"]) {
			VIDEO_TYPE_PROGRESSIVE => "{$video_quality}p",
			default                => throw new \ParseException("Unhandled type of video [{$video_version_item["video_type"]}]"),
		};

		// выставляем поля в зависимости от статуса
		switch ($video_version_item["status"]) {

			case VIDEO_UPLOAD_STATUS_DONE:

				return [
					"status"  => "done",
					"format"  => $format,
					"width"   => $video_version_item["width"],
					"height"  => $video_version_item["height"],
					"url"     => $file_url,
					"size_kb" => $video_version_item["size_kb"],
				];

			case VIDEO_UPLOAD_STATUS_WIP:

				return [
					"status" => "wip",
					"format" => $format,
					"width"  => $video_version_item["width"],
					"height" => $video_version_item["height"],
				];

			case VIDEO_UPLOAD_STATUS_FAIL:

				return [
					"status" => "fail",
					"format" => $format,
					"width"  => $video_version_item["width"],
					"height" => $video_version_item["height"],
				];

			default:
				throw new \ParseException("Unhandled status of video [{$video_version_item["status"]}]");
		}
	}

	/**
	 * Получает URL из part_path
	 */
	protected static function _getUrlByPartPath(string $node_url, string $part_path):string {

		return $node_url . $part_path;
	}

	/**
	 * Получает URL из part_path и добавляет к нему токен загрузки.
	 */
	protected static function _getDownloadUrlByPartPath(string $node_url, string $part_path, string $download_token):string {

		$token = $download_token === "" ? "" : "?token=$download_token";
		return static::_getUrlByPartPath($node_url, $part_path) . $token;
	}

	// получить расширение
	public static function getExtension(string $file_path):string {

		$file_extension = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
		if (mb_strlen($file_extension) > 0) {
			return $file_extension;
		}

		return "";
	}

	// получает file_path из part_path
	public static function getFilePathFromPartPath(string $part_path):string {

		return PATH_WWW . $part_path;
	}

	// получает part_path из file_path
	public static function getPartPathFromFilePath(string $file_path):string {

		return str_replace(PATH_WWW, "", $file_path);
	}

	// получает mime_type файла
	public static function getMimeType(string $file_path):string {

		return mime_content_type($file_path);
	}

	// получает mime_type файла
	public static function getMimeTypeFromContent(string $file_content):string {

		$file_info = finfo_open(FILEINFO_MIME_TYPE);
		$mime_type = finfo_buffer($file_info, $file_content);
		finfo_close($file_info);

		return $mime_type;
	}

	// генерирует part_path
	public static function generatePathPart(string $file_extension, int $company_id):string {

		$dir_depth = 5;
		$path_part = FOLDER_FILE_NAME . "/";

		// добавляем к пути файла его местоположение
		if ($company_id > 0) {
			$path_part = $path_part . "c" . $company_id . "/";
		} else {
			$path_part = $path_part . "pivot" . "/";
		}

		// генерируем рандомные папки по 3 символа
		for ($i = 0; $i < $dir_depth; $i++) {
			$path_part .= mb_substr(bin2hex(openssl_random_pseudo_bytes(3)), 0, 3) . "/";
		}

		// генерируем название для файла
		$path_part .= bin2hex(openssl_random_pseudo_bytes(8));

		// добавляем расширение, если оно есть
		if (mb_strlen($file_extension) > 0) {

			$path_part .= ".{$file_extension}";
		}

		return $path_part;
	}

	// генерирует part_path
	public static function generatePathPartByMigration(string $save_file_path, string $file_extension):string {

		$dir_depth = 5;
		$path_part = $save_file_path . "/";

		// генерируем рандомные папки по 3 символа
		for ($i = 0; $i < $dir_depth; $i++) {
			$path_part .= mb_substr(bin2hex(openssl_random_pseudo_bytes(3)), 0, 3) . "/";
		}

		// генерируем название для файла
		$path_part .= bin2hex(openssl_random_pseudo_bytes(8));

		// добавляем расширение, если оно есть
		if (mb_strlen($file_extension) > 0) {

			$path_part .= ".{$file_extension}";
		}

		return $path_part;
	}

	// генерирует tmp_file_path
	public static function generateTmpPath(string $file_extension = ""):string {

		$tmp_path = "/tmp/" . bin2hex(openssl_random_pseudo_bytes(10));
		if (mb_strlen($file_extension) > 0) {
			$tmp_path .= "." . $file_extension;
		}

		return $tmp_path;
	}

	// получает размер файла
	#[Pure]
	public static function getFileSizeKb(string $file_path):int {

		return ceil(filesize($file_path) / 1024);
	}

	// получает размер файла в байтах
	#[Pure]
	public static function getFileSizeB(string $file_path):int {

		return filesize($file_path);
	}

	// перевод из байтов в килобайты
	#[Pure] public static function convertSizeToKb(int $size_b):int {

		return round($size_b / 1024);
	}

	// получает название файла
	#[Pure] public static function getFileName(string $file_path):string {

		return basename($file_path);
	}

	// получить имя файла из url
	public static function getFileNameFromUrl(string $file_url):string {

		$file_name = self::getFileName($file_url);

		$file_name = parse_url($file_name);

		return $file_name["path"] ?? "";
	}

	// создает директорию для файла
	public static function makeDir(string $file_path):void {

		// убираем из file_path название файла
		$dir = dirname($file_path);

		if (file_exists($dir)) {
			return;
		}

		// рекурсивно создаем папки
		mkdir($dir, 0755, true);
	}

	// сохраняем файл в tmp папку
	public static function saveContentToTmp(string $file_path, string $file_content):void {

		$work_dir     = PATH_TMP;
		$file_subpath = self::_makeSubpath($work_dir, $file_path);

		if ($file_subpath === "") {
			throw new ParseFatalException("try to save to not tmp path");
		}

		self::_save($work_dir, $file_subpath, $file_content);
	}

	/**
	 * Перемещаем файл из tmp папки в папку файлов
	 *
	 * @param string $src_file_path - путь до копируемого файла
	 * @param string $dst_file_path - путь до файла назначения
	 *
	 * @return void
	 */
	public static function moveFromTmpToFiles(string $src_file_path, string $dst_file_path):void {

		$tmp_files_dir  = PATH_TMP;
		$files_work_dir = PATH_WWW . FOLDER_FILE_NAME . "/";

		$src_file_subpath = self::_makeSubpath($tmp_files_dir, $src_file_path);
		$dst_file_subpath = self::_makeSubpath($files_work_dir, $dst_file_path);

		$src_file = File::init($tmp_files_dir, $src_file_subpath);
		$dst_file = File::init($files_work_dir, $dst_file_subpath);

		// если успешно скопировали - удаляем файл, с которого копировали
		if ($src_file->copy($dst_file)) {
			$src_file->delete();
		}

		$dst_file->chmod(0644);
	}

	/**
	 * Перемещаем файл из папки ввв для миграции
	 *
	 * @param string $src_file_path - путь до копируемого файла
	 * @param string $dst_file_path - путь до файла назначения
	 *
	 * @return void
	 */
	public static function moveFromFilesToFilesForMigration(string $src_file_path, string $dst_file_path):void {

		$tmp_files_dir  = PATH_WWW;
		$files_work_dir = PATH_WWW . FOLDER_FILE_NAME . "/";

		$src_file_subpath = self::_makeSubpath($tmp_files_dir, $src_file_path);
		$dst_file_subpath = self::_makeSubpath($files_work_dir, $dst_file_path);

		$src_file = File::init($tmp_files_dir, $src_file_subpath);
		$dst_file = File::init($files_work_dir, $dst_file_subpath);

		// если успешно скопировали - удаляем файл, с которого копировали
		if ($src_file->copy($dst_file)) {
			$src_file->delete();
		}

		$dst_file->chmod(0644);
	}

	/**
	 * Получить подкаталог в рабочей директории
	 */
	protected static function _makeSubpath(string $work_dir, string $file_path):string {

		// узнаем, действительно ли путь ведет в tmp папку
		$file_subpath = "";
		if (substr($file_path, 0, strlen($work_dir)) == $work_dir) {
			$file_subpath = substr($file_path, strlen($work_dir));
		}

		if ($file_subpath === "") {
			throw new ParseFatalException("try to save to not $work_dir dir");
		}

		return $file_subpath;
	}

	// сохраняем файл по пути
	protected static function _save(string $work_dir, string $file_subpath, string $file_content):void {

		// сохраняем содержимое скачиваемого файла в локальный
		$file = File::init($work_dir, $file_subpath);
		$file->write($file_content);

		// устанавливаем разрешение для файла только на чтение
		$file->chmod(0644);
	}

	// получаем хэш файла
	#[Pure] public static function getFileHash(string $file_path):string {

		return sha1_file($file_path);
	}
}