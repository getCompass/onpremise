<?php

namespace Compass\Pivot;

use BaseFrame\System\File;

/**
 * дефолтный класс для работы c конфигами, чтобы получить конфиг один раз
 */
class Type_Lang_Default {

	/**
	 * получаем конфигами
	 *
	 * @return false|array
	 * @mixed
	 */
	protected static function _getConfig(string $lang, string $file_name, bool $is_force_update = false):bool|array {

		$file_dir = self::_getFileDir($lang, $file_name);

		$file      = File::init($file_dir, $file_name);
		$file_path = $file->getFilePath();

		if (isset($GLOBALS[__CLASS__][$file_path]) && !$is_force_update) {
			return $GLOBALS[__CLASS__][$file_path];
		}

		if (!$file->isExists()) {
			return false;
		}

		// получаем из файла конфиг
		$file_content = $file->read();

		if ($file_content === false) {
			return false;
		}

		$config = fromJson($file_content);
		self::_setConfig($file_path, $config);

		return $config;
	}

	/**
	 * устанавливаем конфиг
	 *
	 */
	protected static function _setConfig(string $file_path, array $config):void {

		$GLOBALS[__CLASS__][$file_path] = $config;
	}

	/**
	 * получаем file_path
	 *
	 */
	protected static function _getFileDir(string $lang):string {

		return PIVOT_MODULE_ROOT . "lang/" . $lang . "/";
	}
}