<?php

namespace Compass\Pivot;

/**
 * дефолтный класс для работы c extra дефолтных файлов
 */
class Type_File_Default_Extra {

	/** @var int версия упаковщика */
	protected const _EXTRA_VERSION = 1;

	/** @var array схема extra по версиям */
	protected const _EXTRA_SCHEMA = [

		1 => [
			"replace_file_hash" => "", // hash заменяемого файла
		],
	];

	/**
	 * Создать новую структуру для extra
	 *
	 */
	public static function initExtra():array {

		return [
			"version" => self::_EXTRA_VERSION,
			"extra"   => self::_EXTRA_SCHEMA[self::_EXTRA_VERSION],
		];
	}

	/**
	 * устанавливаем replace_file_hash
	 *
	 * @param array  $extra
	 * @param string $replace_file_hash
	 *
	 * @return array
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	public static function setReplaceFileHash(array $extra, string $replace_file_hash):array {

		$extra = self::_getExtra($extra);

		// обновляем данные
		$extra["extra"]["replace_file_hash"] = $replace_file_hash;
		return $extra;
	}

	/**
	 * получаем replace_file_hash
	 *
	 * @param array $extra
	 *
	 * @return string
	 */
	public static function getReplaceFileHash(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["replace_file_hash"];
	}

	# region protected

	/**
	 * Получить актуальную структуру для extra
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	protected static function _getExtra(array $extra):array {

		// если экстра была пустая
		if (!isset($extra["version"])) {

			$extra["extra"]   = array_merge(static::_EXTRA_SCHEMA[static::_EXTRA_VERSION], []);
			$extra["version"] = static::_EXTRA_VERSION;
		}

		// если версия не совпадает - дополняем её до текущей
		if ((int) $extra["version"] !== static::_EXTRA_VERSION) {

			$extra["extra"]   = array_merge(static::_EXTRA_SCHEMA[static::_EXTRA_VERSION], $extra["extra"]);
			$extra["version"] = static::_EXTRA_VERSION;
		}

		return $extra;
	}
}