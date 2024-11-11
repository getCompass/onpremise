<?php

namespace Compass\Userbot;

use BaseFrame\System\File;

/**
 * класс для системных админских функций
 */
class Type_System_Admin {

	/**
	 * функция для записи логов в /logs/info/log_name и /log/__admin.log
	 * принимает log_name - имя лога
	 * что нужно записать
	 * $notice - если передать true, лог также запишется в __admin.log
	 *
	 * @mixed
	 */
	public static function log(string $log_name, $txt, bool $notice = false):void {

		// если пришел массив - то преваращем его в строку и аргументы разделяем между собой
		if (is_array($txt)) {
			$txt = formatArgs($txt);
		}

		// соединяем дату и строку для читабельности
		// пишем лог в /logs/info/log_name.log все что передали в $txt

		$date = date(DATE_FORMAT_FULL_S, time());
		$txt  = "{$date}\t{$txt}\n";
		File::init(PATH_LOGS . "info/", mb_strtolower($log_name) . ".log")->write($txt, true);

		// если массив и нужны уведомления в "__admin.log" надо поставить на true
		if ($notice === true) {
			@file_put_contents(LOG_ADMIN, $txt, FILE_APPEND);
		}
	}
}