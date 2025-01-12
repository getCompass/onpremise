<?php

namespace Compass\FileBalancer;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * вспомогательные функции для файлов
 */
class Type_File_Utils {

	// подготавливает файл к передаче в Apiv1_Format
	// @long --- switch...case
	public static function prepareFileForFormat(array $file_row, string $node_url, int $user_id):array {

		// если файл загружен для использования на CDN, то подменяем url отдачи файла с CDN серверов
		if ($file_row["is_cdn"] == 1) {
			$node_url = CDN_URL;
		}

		$output = [
			"file_map"       => $file_row["file_map"],
			"size_kb"        => $file_row["size_kb"],
			"created_at"     => $file_row["created_at"],
			"type"           => $file_row["file_type"],
			"url"            => self::getDownloadUrlByPartPath($node_url, $file_row["extra"]["original_part_path"]),
			"file_name"      => $file_row["file_name"],
			"file_extension" => $file_row["file_extension"],
			"file_hash"      => $file_row["file_hash"],
			"content"        => $file_row["content"],
		];

		$data = [];

		// если есть родительский файл от файла
		if (isset($file_row["extra"]["parent_file_key"])) {
			$data["parent"]["file_key"] = $file_row["extra"]["parent_file_key"];
		}

		// выбираем тип файла и подгружаем его
		switch ($file_row["file_type"]) {

			case FILE_TYPE_IMAGE:

				// добавляем оригинальное изображение в image_size
				$file_row["extra"]["image_size_list"][] = $file_row["extra"]["original_image_item"];
				$data["image_version_list"]             = self::_makeImageVersionList($node_url, $file_row["extra"]["image_size_list"]);
				break;

			case FILE_TYPE_AUDIO:

				$data["duration"]           = $file_row["extra"]["duration"];
				$data["original_part_path"] = $file_row["extra"]["original_part_path"] ?? "";
				$data["status"]             = $file_row["extra"]["status"] ?? 1;
				$data["convert_part_path"]  = $file_row["extra"]["convert_part_path"] ?? "";
				$data["url_convert_path"]   = isset($file_row["extra"]["convert_part_path"]) ? self::getDownloadUrlByPartPath($node_url, $file_row["extra"]["convert_part_path"]) : "";
				$data["name_convert_file"]  = $file_row["extra"]["name_convert_file"] ?? "";
				break;

			case FILE_TYPE_VOICE:

				$data["duration"]    = $file_row["extra"]["duration"];
				$data["duration_ms"] = Type_File_Voice::getDurationInMs($file_row["extra"]);
				$data["waveform"]    = $file_row["extra"]["waveform"];
				$data["is_listen"]   = Type_File_Voice::isListenByUser($file_row["extra"], $user_id) ? 1 : 0;
				break;

			case FILE_TYPE_VIDEO:

				$data["original_video_info"] = $file_row["extra"]["original_video_item"];
				$data["image_version_list"]  = self::_makeImageVersionList($node_url, $file_row["extra"]["preview_size_list"]);
				$data["duration"]            = $file_row["extra"]["duration"];
				$data["preview"]             = self::getDownloadUrlByPartPath($node_url, $file_row["extra"]["preview_original_part_path"]);
				$data["video_version_list"]  = self::_makeVideoVersionList($node_url, $file_row["extra"]["video_version_list"]);
				break;

			default:
				$data = [];
		}

		$output["data"] = $data;
		return $output;
	}

	// получает URL из part_path
	public static function getUrlByPartPath(string $node_url, string $part_path):string {

		return $node_url . $part_path;
	}

	/**
	 * Получает URL из part_path и добавляет к нему токен загрузки.
	 */
	public static function getDownloadUrlByPartPath(string $node_url, string $part_path):string {

		return static::_attachDownloadToken(static::getUrlByPartPath($node_url, $part_path), $part_path);
	}

	// video_version_list в файлах-видео
	protected static function _makeVideoVersionList(string $node_url, array $video_version_list):array {

		$output = [];

		// проходимся по всему массиву и приводим формат
		foreach ($video_version_list as $version_key => $v) {

			// получаем название формата
			$format = self::_tryGetVideoFormat($v, $version_key);

			// формируем ответ
			$output[] = self::_makeOutput($v, $format, $node_url);
		}

		return $output;
	}

	// пробуем получить формат видео
	protected static function _tryGetVideoFormat(array $video, string $version_key):string {

		// switch по типу видео - получаем название формата
		switch ($video["video_type"]) {

			case VIDEO_TYPE_PROGRESSIVE:

				// получаем формат видео
				// ключ выглядит так - 720_1, нужна первая часть
				$video_quality = (int) explode("_", $version_key)[0];

				// устанавливаем формат
				return "{$video_quality}p";

			default:
				throw new parseException("Unhandled type of video [{$video["video_type"]}]");
		}
	}

	// формируем ответ в зависимости от статуса
	// @long --- switch...case
	protected static function _makeOutput(array $video, string $format, string $node_url):array {

		// выставляем поля в зависимости от статуса
		switch ($video["status"]) {

			case VIDEO_UPLOAD_STATUS_DONE:

				return [
					"status"  => "done",
					"format"  => $format,
					"url"     => self::getDownloadUrlByPartPath($node_url, $video["part_path"]),
					"width"   => $video["width"],
					"height"  => $video["height"],
					"size_kb" => $video["size_kb"],
				];

			case VIDEO_UPLOAD_STATUS_WIP:

				return [
					"status" => "wip",
					"format" => $format,
					"width"  => $video["width"],
					"height" => $video["height"],
				];

			case VIDEO_UPLOAD_STATUS_FAIL:

				return [
					"status" => "fail",
					"format" => $format,
					"width"  => $video["width"],
					"height" => $video["height"],
				];

			default:
				throw new parseException("Unhandled status of video [{$video["status"]}]");
		}
	}

	// image_version_list в файлах-изображениях
	protected static function _makeImageVersionList(string $node_url, array $image_version_list):array {

		// проходимся по всему массиву и приводим формат
		foreach ($image_version_list as $k => $v) {
			$image_version_list[$k] = self::_getImageItem($node_url, $v);
		}

		return $image_version_list;
	}

	// формируем image item
	protected static function _getImageItem(string $node_url, array $image_version_item):array {

		return [
			"url"     => self::getDownloadUrlByPartPath($node_url, $image_version_item["part_path"]),
			"width"   => $image_version_item["width"],
			"height"  => $image_version_item["height"],
			"size_kb" => $image_version_item["size_kb"],
		];
	}

	/**
	 * Добавляет к файлу токен загрузки при необходимости.
	 */
	protected static function _attachDownloadToken(string $url_path, string $part_path):string {

		if (COMPANY_ID === 0 || (bool) IS_FILE_AUTH_RESTRICTION_ENABLED === false) {
			return $url_path;
		}

		return $url_path . "?token=" . static::makeDownloadToken($part_path);
	}

	/**
	 * Формирует токен загрузки для файла.
	 */
	public static function makeDownloadToken(string $part_path):string {

		// проверяем ид компании и статус проверки авторизации для файлов
		if (COMPANY_ID === 0 || (bool) IS_FILE_AUTH_RESTRICTION_ENABLED === false) {
			return "";
		}

		// nosemgrep
		$uniq = sha1($part_path);

		$token = [
			"u" => $uniq[3] . $uniq[7] . $uniq[11] . $uniq[15],
			"c" => COMPANY_ID,
			"e" => ENTRYPOINT_DOMINO . "/api/socket/company/",
		];

		return urlencode(base64_encode(CrypterProvider::get("download_token")->encrypt(toJson($token))));
	}
}