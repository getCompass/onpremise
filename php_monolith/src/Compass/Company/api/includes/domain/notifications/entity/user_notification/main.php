<?php

namespace Compass\Company;

/**
 * Класс для взаимодействия с уведомлениями пользователей
 */
class Domain_Notifications_Entity_UserNotification_Main {

	/**
	 * Проверяем время на валидность
	 *
	 * @throws cs_SnoozeTimeIntervalLessThenMinute
	 */
	public static function assertValidTimeInterval(int $time_interval):void {

		if ($time_interval < 0) {
			throw new cs_SnoozeTimeIntervalLessThenMinute();
		}
	}

	/**
	 * Получаем конечное значение snoozed until
	 */
	public static function makeSnoozedUntil(int $time_at, int $max_time_limit, int $snoozed_until, int $interval_minutes):int {

		// замораживаем уведомления, стартуя от текущего времени, если у пользователя уведомления не отключены
		if ($snoozed_until < $time_at) {
			$snoozed_until = $time_at;
		}

		// получаем новое время до скольки нужно отключить уведомления
		$snoozed_until = $snoozed_until + $interval_minutes * 60;

		// если время на которое нужно отключить уведомления больше максимального
		if ($snoozed_until > $max_time_limit) {
			$snoozed_until = $max_time_limit;
		}

		return $snoozed_until;
	}

	/**
	 * Возвращаем максимально значение таймера
	 */
	public static function getMaxTimeLimit(int $time_at):int {

		return $time_at + DAY1 * 99 + (DAY1 - 1); // максимальное значение таймера
	}
}
