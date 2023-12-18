<?php

namespace Compass\Conversation;

/**
 * дефолтный класс для работы c языковыми конфигами
 */
class Type_Lang_Default {

	/**
	 * получаем конфигами
	 *
	 * @return false|array
	 * @mixed
	 */
	protected static function _getConfig(string $lang, string $file_name, bool $is_force_update = false):bool|array {

		$file_path = self::_getFilePath($lang, $file_name);

		if (isset($GLOBALS[__CLASS__][$file_path]) && !$is_force_update) {
			return $GLOBALS[__CLASS__][$file_path];
		}

		if (!file_exists($file_path) && !is_file($file_path)) {
			return false;
		}

		// получаем из файла конфиг
		$file_content = file_get_contents($file_path);

		if ($file_content === false) {
			return false;
		}

		$config = fromJson($file_content);
		self::_setConfig($lang, $file_name, $config);

		return $config;
	}

	/**
	 * устанавливаем конфиг
	 *
	 */
	protected static function _setConfig(string $lang, string $file_name, array $config):void {

		$file_path                      = self::_getFilePath($lang, $file_name);
		$GLOBALS[__CLASS__][$file_path] = $config;
	}

	/**
	 * получаем file_path
	 *
	 */
	protected static function _getFilePath(string $lang, string $file_name):string {

		return PATH_ROOT . "lang/" . $lang . "/" . $file_name;
	}
}