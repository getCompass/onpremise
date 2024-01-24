<?php

namespace Compass\FileNode;

/**
 * Класс для форматирование сущностей под формат API
 *
 * В коде мы оперируем своими структурами и понятиями
 * К этому классу обращаемся строго отдачей результата в API
 * Для форматирования стандартных сущностей
 */
class Apiv1_Format {

	protected const _FILE_TYPE_NAME = [
		FILE_TYPE_DEFAULT  => "file",
		FILE_TYPE_IMAGE    => "image",
		FILE_TYPE_VIDEO    => "video",
		FILE_TYPE_AUDIO    => "audio",
		FILE_TYPE_DOCUMENT => "document",
		FILE_TYPE_ARCHIVE  => "archive",
		FILE_TYPE_VOICE    => "voice",
	];

	// файл
	//@long - switch...case
	public static function file(array $file_row):array {

		$output = [
			"file_key"       => (string) $file_row["file_key"],
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

				$data["image_version_list"] = (array) self::_getImageVersionList($file_row["data"]["image_version_list"]);
				break;

			case FILE_TYPE_AUDIO:

				$status            = $file_row["data"]["status"] ?? 1;
				$convert_part_path = $file_row["data"]["convert_part_path"] ?? "";
				$name_convert_file = $file_row["data"]["name_convert_file"] ?? "";

				$data["duration"]           = (int) $file_row["data"]["duration"];
				$data["original_part_path"] = (string) $file_row["data"]["original_part_path"];
				$data["status"]             = (int) $status;
				$data["convert_part_path"]  = (string) $convert_part_path;
				$data["name_convert_file"]  = (string) $name_convert_file;
				break;

			case FILE_TYPE_VOICE:

				$data["duration"]    = (int) $file_row["data"]["duration"];
				$data["duration_ms"] = (int) $file_row["data"]["duration_ms"];
				$data["waveform"]    = (array) self::_getWaveform($file_row["data"]["waveform"]);
				$data["is_listen"]   = (int) $file_row["data"]["is_listen"];
				break;

			case FILE_TYPE_VIDEO:

				$data["original_video_info"] = (object) self::_getOriginalVideoInfo($file_row["data"]["original_video_info"]);
				$data["video_version_list"]  = (array) self::_getVideoVersionList($file_row["data"]["video_version_list"]);
				$data["image_version_list"]  = (array) self::_getImageVersionList($file_row["data"]["image_version_list"]);
				$data["duration"]            = (int) $file_row["data"]["duration"];
				$data["preview"]             = (string) $file_row["data"]["preview"];
				break;

			default:
				$data = [];
		}

		$output["data"] = (object) $data;

		return $output;
	}

	// video_version_list в файлах-изображениях
	protected static function _getVideoVersionList(array $video_version_list):array {

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

	// основная инфа о оригинальном видео
	protected static function _getOriginalVideoInfo(array $original_video_info):array {

		return [
			"width"   => (int) $original_video_info["width"],
			"height"  => (int) $original_video_info["height"],
			"size_kb" => (int) $original_video_info["size_kb"],
		];
	}

	// image_version_list в файлах-изображениях
	protected static function _getImageVersionList(array $image_version_list):array {

		// проходимся по всему массиву и приводим формат
		foreach ($image_version_list as $k => $v) {

			$image_version_list[$k] = [
				"url"     => (string) $v["url"],
				"width"   => (int) $v["width"],
				"height"  => (int) $v["height"],
				"size_kb" => (int) $v["size_kb"],
			];
		}

		return $image_version_list;
	}

	// waveform в голосовых аудио
	protected static function _getWaveform(array $waveform):array {

		// проходимся по всему массиву и приводим формат
		foreach ($waveform as $k => $v) {
			$waveform[$k] = (int) $v;
		}

		return $waveform;
	}
}