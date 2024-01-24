<?php

namespace Compass\FileNode;

/**
 * Класс для работы с аудио
 *
 * структура для поля extra:
 * {
 *    "version": 1,
 *    "original_part_path": "/files/dd/ee/audio.mp3",
 *    "duration": 5
 * }
 */
class Type_File_Audio_Extra {

	// текущяя версия структуры
	protected const _EXTRA_VERSION = 1;

	// формирует структуру extra
	public static function getExtra(string $original_part_path, int $company_id, string $company_url, int $duration, bool $is_need_convert = false):array {

		$extra = [
			"version"            => self::_EXTRA_VERSION,
			"original_part_path" => $original_part_path,
			"duration"           => $duration,
			"company_id"         => $company_id,
			"company_url"        => $company_url,
			"status"             => Type_File_Main::STATUS_OK,
		];

		if ($is_need_convert) {

			$extra["convert_to"] = Type_File_Audio_Main::CONVERT_TYPE;
			$extra["status"]     = Type_File_Main::STATUS_CONVERT;
		}

		return $extra;
	}
}