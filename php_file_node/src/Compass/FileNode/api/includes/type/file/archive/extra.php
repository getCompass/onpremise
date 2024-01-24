<?php

namespace Compass\FileNode;

/**
 * Класс для работы с extra файлов
 *
 * структура для поля extra:
 * {
 *    "version": 1,
 *    "original_part_path": "/files/dd/ee/archive.rar",
 * }
 */
class Type_File_Archive_Extra {

	// текущая версия структуры
	protected const _EXTRA_VERSION = 1;

	// получаем extra
	public static function getExtra(string $original_part_path, int $company_id, string $company_url):array {

		return [
			"version"            => self::_EXTRA_VERSION,
			"original_part_path" => $original_part_path,
			"company_id"         => $company_id,
			"company_url" => $company_url
		];
	}
}