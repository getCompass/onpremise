<?php

namespace Compass\Pivot;

/**
 * Отключаем уведомления на определенное время
 */
class Domain_User_Action_Notifications_DoDisable {

	/**
	 * Отключаем уведомления
	 *
	 * @throws cs_NotificationsIntervalLessThenMinute
	 * @throws cs_NotificationsShutdownLimitExceeded
	 */
	public static function do(int $user_id, int $interval_minutes):int {

		// если интервал меньше минуты
		if ($interval_minutes < 1) {
			throw new cs_NotificationsIntervalLessThenMinute();
		}

		// получаем состояние уведомлений пользователя
		$notification_preferences = Type_User_Notifications::getPreferences($user_id);

		$time_at        = time(); // записываем текущее время
		$max_time_limit = $time_at + DAY1 * 99 + (DAY1 - 1); // максимальное значение таймера

		// получаем новое значение snoozed_until
		$new_snoozed_until = self::_makeNewSnoozedUntil($time_at, $max_time_limit, $notification_preferences["snoozed_until"], $interval_minutes);

		// обновляем в базе
		Type_User_Notifications::snooze($user_id, $new_snoozed_until);

		// отправляем событие snoozed_timer_changed на все устройства пользователя
		Gateway_Bus_SenderBalancer::snoozedTimerChanged($user_id, $new_snoozed_until);

		// если уперлись в максимальное время отключения уведомлений
		if ($new_snoozed_until == $max_time_limit) {
			throw new cs_NotificationsShutdownLimitExceeded($max_time_limit);
		}

		return $new_snoozed_until;
	}

	/**
	 * получаем новое значение snoozed until
	 *
	 */
	protected static function _makeNewSnoozedUntil(int $time_at, int $max_time_limit, int $snoozed_until, int $interval_minutes):int {

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
}