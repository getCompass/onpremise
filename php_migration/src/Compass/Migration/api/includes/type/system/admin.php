<?php

namespace Compass\Migration;

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
	 * @param string $log_name
	 * @param mixed  $txt
	 * @param bool   $notice
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
		@file_put_contents(PATH_LOGS . "info/" . mb_strtolower($log_name) . ".log", $txt, FILE_APPEND);

		// если массив и нужны уведомления в "__admin.log" надо поставить на true
		if ($notice === true) {
			@file_put_contents(LOG_ADMIN, $txt, FILE_APPEND);
		}
	}
}