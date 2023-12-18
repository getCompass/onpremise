<?php

namespace Compass\Pivot;

/**
 * Включаем уведомления на определенное время
 */
class Domain_User_Action_Notifications_DoEnable {

	/**
	 * Включаем уведомления
	 *
	 * @throws \queryException
	 */
	public static function do(int $user_id):void {

		$notification_preferences = Type_User_Notifications::getPreferences($user_id);

		// проверяем что уведомления включены
		if ($notification_preferences["snoozed_until"] == 0) {
			return;
		}

		// размораживаем уведомления
		Type_User_Notifications::unsnooze($user_id);

		// отправляем событие snoozed_timer_changed на все устройства пользователя
		Gateway_Bus_SenderBalancer::snoozedTimerChanged($user_id, 0);
	}
}