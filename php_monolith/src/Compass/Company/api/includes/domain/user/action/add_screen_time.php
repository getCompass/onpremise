<?php

namespace Compass\Company;

use BaseFrame\Exception\Gateway\BusFatalException;

/**
 * Действие для сохранения экранного времени
 */
class Domain_User_Action_AddScreenTime {

	/**
	 * Выполняем
	 *
	 * @param int    $user_id
	 * @param string $local_date
	 * @param string $local_time
	 *
	 * @return void
	 * @throws BusFatalException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function do(int $user_id, string $local_date, string $local_time):void {

		if (mb_strlen($local_date) < 1 || mb_strlen($local_time) < 1) {
			return;
		}

		// сохраняем экранное время
		$local_online_at = self::_getLocalOnlineAt($local_date, $local_time);
		Gateway_Bus_Rating_Main::addScreenTime($user_id, $local_online_at, USER_SCREEN_TIME_SECONDS);
	}

	/**
	 * Получаем локальное время когда пользователь был онлайн
	 * !!! если что-то меняется - также нужно в php_conversation и php_thread поменять
	 *
	 * @param string $local_date
	 * @param string $local_time
	 *
	 * @return string
	 */
	protected static function _getLocalOnlineAt(string $local_date, string $local_time):string {

		// разбиваем время формата 11:32:45
		$tt = explode(":", $local_time);

		// форматируем минуты до ближайшей 15-ти минутки
		$minutes = (int) $tt[1];
		if ($minutes >= 0 && $minutes < 15) {
			$tt[1] = "00";
		} elseif ($minutes >= 15 && $minutes < 30) {
			$tt[1] = "15";
		} elseif ($minutes >= 30 && $minutes < 45) {
			$tt[1] = "30";
		} elseif ($minutes >= 45) {
			$tt[1] = "45";
		}

		// собираем новое форматированное время без секунд
		$formatted_local_time = "{$tt[0]}:{$tt[1]}";

		// формируем локальное время в формате: 05.07.2023 11:30
		return "{$local_date} {$formatted_local_time}";
	}
}