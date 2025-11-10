<?php

namespace Compass\FileBalancer;

/**
 * класс для форматирование сущностей под формат API
 *
 * в коде мы оперируем своими структурами и понятиями
 * к этому классу обращаемся строго отдачей результата в API
 * для форматирования стандартных сущностей
 */
class Apiv1_Format
{
	protected const _FILE_TYPE_NAME = [
		FILE_TYPE_DEFAULT  => "file",
		FILE_TYPE_IMAGE    => "image",
		FILE_TYPE_VIDEO    => "video",
		FILE_TYPE_AUDIO    => "audio",
		FILE_TYPE_DOCUMENT => "document",
		FILE_TYPE_ARCHIVE  => "archive",
		FILE_TYPE_VOICE    => "voice",
	];

	// -------------------------------------------------------
	// PUBLIC
	// -------------------------------------------------------

	/**
	 * Файл с содержимым.
	 */
	public static function fileWithContent(array $file_row): array
	{

		$file = static::file($file_row);

		$file["content"] = $file_row["content"] ?? "";
		return $file;
	}

	// файл
	//@long --- switch...case
	public static function file(array $file_row): array
	{

		$output = [
			"file_map"       => (string) $file_row["file_map"],
			"is_deleted"     => (int) $file_row["is_deleted"],
			"status"         => (string) Type_File_Main::FILE_STATUS_NAME[$file_row["status"]],
			"size_kb"        => (int) $file_row["size_kb"],
			"created_at"     => (int) $file_row["created_at"],
			"type"           => (string) self::_FILE_TYPE_NAME[$file_row["type"]],
			"url"            => (string) $file_row["url"],
			"file_name"      => (string) $file_row["file_name"],
			"file_extension" => (string) $file_row["file_extension"],
			"file_hash"      => (string) $file_row["file_hash"],
		];

		$data = [];

		// если есть родительский файл от файла
		if (isset($file_row["data"]["parent"]["file_key"])) {
			$data["parent"]["file_key"] = $file_row["data"]["parent"]["file_key"];
		}

		// выбираем тип сообщения и подгружаем его
		switch ($file_row["type"]) {

			case FILE_TYPE_IMAGE:

				$data["image_version_list"] = (array) self::_doFormatImageVersionList($file_row["data"]["image_version_list"]);

				if (isset($file_row["data"]["cropped_image_version_list"])) {
					$data["cropped_image_version_list"] = (array) self::_doFormatImageVersionList($file_row["data"]["cropped_image_version_list"]);
				}
				break;

			case FILE_TYPE_AUDIO:

				$data["duration"]           = (int) $file_row["data"]["duration"];
				$data["original_part_path"] = (string) $file_row["data"]["original_part_path"];
				$data["status"]             = (int) $file_row["data"]["status"];
				$data["convert_part_path"]  = (string) $file_row["data"]["convert_part_path"];
				$data["url_convert_path"]   = (string) $file_row["data"]["url_convert_path"];
				$data["name_convert_file"]  = (string) $file_row["data"]["name_convert_file"];
				break;

			case FILE_TYPE_VOICE:

				$data["duration"]    = (int) $file_row["data"]["duration"];
				$data["duration_ms"] = (int) $file_row["data"]["duration_ms"];
				$data["waveform"]    = (array) self::_doFormatWaveform($file_row["data"]["waveform"]);
				$data["is_listen"]   = (int) $file_row["data"]["is_listen"];
				break;

			case FILE_TYPE_VIDEO:

				$data["original_video_info"] = (object) self::_doFormatOriginalVideoInfo($file_row["data"]["original_video_info"]);
				$data["video_version_list"]  = (array) self::_doFormatvideoVersionList($file_row["data"]["video_version_list"]);
				$data["image_version_list"]  = (array) self::_doFormatImageVersionList($file_row["data"]["image_version_list"]);
				$data["duration"]            = (int) $file_row["data"]["duration"];
				$data["preview"]             = (string) $file_row["data"]["preview"];
				break;

			default:
				$data = [];
		}

		$output["data"] = (object) $data;

		return $output;
	}

	// waveform в голосовых аудио
	protected static function _doFormatWaveform(array $waveform): array
	{

		// проходимся по всему массиву и приводим формат
		foreach ($waveform as $k => $v) {
			$waveform[$k] = (int) $v;
		}

		return $waveform;
	}

	// основная инфа о оригинальном видео
	protected static function _doFormatOriginalVideoInfo(array $original_video_info): array
	{

		return [
			"width"   => (int) $original_video_info["width"],
			"height"  => (int) $original_video_info["height"],
			"size_kb" => (int) $original_video_info["size_kb"],
		];
	}

	// video_version_list в файлах-изображениях
	protected static function _doFormatVideoVersionList(array $video_version_list): array
	{

		$output = [];

		// проходимся по всему массиву и приводим формат
		foreach ($video_version_list as $v) {

			$video_version_item = [
				"status" => (string) $v["status"],
				"format" => (string) $v["format"],
				"width"  => (int) $v["width"],
				"height" => (int) $v["height"],
			];

			if ($v["status"] == "done") {

				$video_version_item["url"]     = (string) $v["url"];
				$video_version_item["size_kb"] = (int) $v["size_kb"];
			}

			$output[] = $video_version_item;
		}

		return $output;
	}

	// image_version_list в файлах-изображениях
	protected static function _doFormatImageVersionList(array $image_version_list): array
	{

		// проходимся по всему массиву и приводим формат
		foreach ($image_version_list as $k => $v) {
			$image_version_list[$k] = self::_doFormatImageVersionItem($v);
		}

		return $image_version_list;
	}

	// получаем один элемент image version
	protected static function _doFormatImageVersionItem(array $image_version_item): array
	{

		return [
			"url"     => (string) $image_version_item["url"],
			"width"   => (int) $image_version_item["width"],
			"height"  => (int) $image_version_item["height"],
			"size_kb" => (int) $image_version_item["size_kb"],
		];
	}

	// отношение к типу файла
	public static function fileRel(array $rel): array
	{

		$output = [];

		// проходимся по массиву и приводим в формат
		foreach ($rel as $k => $v) {
			$output[self::_FILE_TYPE_NAME[$k]] = $v;
		}

		return $output;
	}
}
