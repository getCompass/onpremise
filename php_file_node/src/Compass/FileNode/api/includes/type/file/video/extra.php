<?php

namespace Compass\FileNode;

/**
 * Класс для работы с extra видео
 *
 * структура для поля extra:
 * {
 *    "version": 1,
 *    "original_part_path": "/files/dd/ee/video.mp4",
 *    "original_video_item": {
 *          "width": 720,
 *          "height": 360,
 *          "ration": 1/2,
 *          "size_kb": 1024
 *    }
 *    "preview_original_part_path": "/files/dd/ee/video.jpg",
 *    "preview_size_list": [
 *          {
 *                "part_path": "/files/dd/ee/video_w320.jpg",
 *                  "width": 320,
 *                  "height": 180,
 *                  "size_kb": 81
 *          }
 *    ],
 *    "duration": 5
 * }
 */
class Type_File_Video_Extra {

	// текущяя версия структуры
	protected const _EXTRA_VERSION = 1;

	// устанавливает status = DONE для версии видео
	public static function setVideoVersionItemStatus(array $extra, string $video_item_key, int $size_kb, bool $is_success):array {

		// меняем статус обработки видео
		if ($is_success) {

			$extra["video_version_list"][$video_item_key]["status"]  = VIDEO_UPLOAD_STATUS_DONE;
			$extra["video_version_list"][$video_item_key]["size_kb"] = $size_kb;
		} else {
			$extra["video_version_list"][$video_item_key]["status"] = VIDEO_UPLOAD_STATUS_FAIL;
		}

		return $extra;
	}

	// формирует структуру extra
	public static function getExtra(string $original_part_path, int $company_id, string $company_url, array $original_video_item, string $preview_original_part_path, array $preview_extra, array $video_version_list, int $duration):array {

		return [
			"version"                    => self::_EXTRA_VERSION,
			"original_part_path"         => $original_part_path,
			"original_video_item"        => $original_video_item,
			"preview_original_part_path" => $preview_original_part_path,
			"preview_size_list"          => $preview_extra["image_size_list"],
			"video_version_list"         => $video_version_list,
			"duration"                   => $duration,
			"company_id"                 => $company_id,
			"company_url"                => $company_url,
		];
	}

	/**
	 * получаем video_version_list из extra
	 *
	 * @param array $extra
	 *
	 * @return array
	 */
	public static function getVideoVersionList(array $extra):array {

		if (!isset($extra["video_version_list"])) {
			return [];
		}

		return $extra["video_version_list"];
	}

	/**
	 * получаем preview_size_list из extra
	 *
	 * @param array $extra
	 *
	 * @return array
	 */
	public static function getPreviewSizeList(array $extra):array {

		if (!isset($extra["preview_size_list"])) {
			return [];
		}

		return $extra["preview_size_list"];
	}

	/**
	 * записываем preview_size_list в extra
	 *
	 * @param array $extra
	 *
	 * @return array
	 */
	public static function setPreviewSizeList(array $extra, array $preview_size_list):array {

		$extra["preview_size_list"] = $preview_size_list;

		return $extra;
	}

	/**
	 * получаем preview_original_part_path из extra
	 *
	 * @param array $extra
	 *
	 * @return string
	 */
	public static function getPreviewOriginalPartPath(array $extra):string {

		if (!isset($extra["preview_original_part_path"])) {
			return "";
		}

		return $extra["preview_original_part_path"];
	}

	/**
	 * записываем preview_original_part_path в extra
	 *
	 * @return array
	 */
	public static function setPreviewOriginalPartPath(array $extra, string $original_part_path):array {

		$extra["preview_original_part_path"] = $original_part_path;

		return $extra;
	}
}