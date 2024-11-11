<?php

namespace Compass\Conversation;

use BaseFrame\System\File;

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

		$file = self::_getFile($lang, $file_name);

		if (isset($GLOBALS[__CLASS__][$file->getFilePath()]) && !$is_force_update) {
			return $GLOBALS[__CLASS__][$file->getFilePath()];
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
		self::_setConfig($lang, $file_name, $config);

		return $config;
	}

	/**
	 * устанавливаем конфиг
	 *
	 */
	protected static function _setConfig(string $lang, string $file_name, array $config):void {

		$file                                     = self::_getFile($lang, $file_name);
		$GLOBALS[__CLASS__][$file->getFilePath()] = $config;
	}

	/**
	 * получаем file
	 *
	 */
	protected static function _getFile(string $lang, string $file_name):File {

		return File::init(PATH_ROOT . "lang/" . $lang . "/", $file_name);
	}
}