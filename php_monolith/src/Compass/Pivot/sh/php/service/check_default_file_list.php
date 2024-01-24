<?php

namespace Compass\Pivot;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1);
set_time_limit(0);

/**
 * Проверяем что все файлы были загружены на ноду
 */
class CheckDefaultFileList {

	protected const _PATH_TO_FILE = PATH_WWW . "default_file/";

	/**
	 * Запускаем скрипт
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function run():void {

		$default_file_list = Type_File_Default::getDefaultFileList();

		// проверяем наличие
		foreach ($default_file_list as $default_file) {
			self::checkFile($default_file["dictionary_key"], $default_file["file_name"], $default_file["file_hash"]);
		}

		console(greenText("Все файл найдены"));
	}

	/**
	 * Проверяем наличие актуально загруженного файла
	 *
	 * @mixed
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function checkFile(string $dictionary_key, string $file_name, string $file_hash_config):void {

		// получаем хеш файла
		$file_path = self::_PATH_TO_FILE . $file_name;
		$file_hash = Type_File_Utils::getFileHash($file_path);

		// проверяем что хеш файла совпадает с хешом в конфиге
		if ($file_hash_config !== $file_hash) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("file config hash does not match {$dictionary_key}");
		}

		// сравниваем его с хешом файла в базе
		try {
			$file_row = Gateway_Db_PivotSystem_DefaultFileList::get($dictionary_key);
		} catch (\cs_RowIsEmpty) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("file not found {$dictionary_key}");
		}

		if ($file_hash !== $file_row->file_hash) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("uploaded file hash does not match {$dictionary_key}");
		}
	}
}

// запускаем скрипт
CheckDefaultFileList::run();