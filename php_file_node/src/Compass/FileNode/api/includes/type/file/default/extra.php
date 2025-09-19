<?php

namespace Compass\FileNode;

/**
 * Класс для работы с extra обычных файлов
 *
 * структура для поля extra:
 * {
 *    "version": 1,
 *    "original_part_path": "pWY/iqn/prn/3KI/9yJ/fGpWc3Elb1lg7ZB7",
 * }
 */
class Type_File_Default_Extra
{
	// текущая версия структуры
	protected const _EXTRA_VERSION = 1;

	// формирует структуру extra
	public static function getExtra(string $original_part_path, int $company_id, string $company_url): array
	{

		return [
			"version"            => self::_EXTRA_VERSION,
			"original_part_path" => $original_part_path,
			"company_id"         => $company_id,
			"company_url"        => $company_url,
		];
	}

	/**
	 * Возвращаем company_id
	 */
	public static function getCompanyId(array $extra): int
	{

		return $extra["company_id"];
	}

	/**
	 * Возвращаем company_url
	 */
	public static function getCompanyUrl(array $extra): string
	{

		return $extra["company_url"] ?? "";
	}
}
