<?php

namespace Compass\Pivot;

use BaseFrame\System\File;

require_once __DIR__ . "/../../../../../../start.php";

ini_set("memory_limit", "4096M");
ini_set("display_errors", 1); // nosemgrep
set_time_limit(0);

/**
 * Дожидаемся появления default-файлов приложения
 */
class WaitDefaultFiles {

	protected const _PATH_TO_FILE = PATH_WWW . "default_file/";

	protected const _WAIT_FILES_TIMEOUT = 300;

	/**
	 * Выполняем
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function run():void {

		$manifest_file = File::init(self::_PATH_TO_FILE, "manifest.json");

		$end_at = time() + self::_WAIT_FILES_TIMEOUT;
		while (time() <= $end_at) {

			if ($manifest_file->isExists()) {

				console(greenText("Проверка default-файлов выполнена успешно."));
				return;
			}
			sleep(1);
		}

		throw new \BaseFrame\Exception\Domain\ParseFatalException("default-files not found");
	}
}

WaitDefaultFiles::run();