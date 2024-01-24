<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Базовый класс для действия для отключения уведомлений
 */
class Domain_Notifications_Action_DoDisable {

	/**
	 * Выполняем action
	 *
	 * @throws \busException
	 * @throws cs_NotificationsSnoozeTimeLimitExceeded
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(int $user_id, int $interval_minutes):int {

		$time_at        = time();
		$max_time_limit = Domain_Notifications_Entity_UserNotification_Main::getMaxTimeLimit($time_at);

		$new_snoozed_until = self::_setSnoozedUntil($user_id, $interval_minutes, $time_at, $max_time_limit);

		// сбрасываем кэш в микросервисе для пользователя
		Gateway_Bus_Sender::clearUserNotificationCache($user_id);

		// отправляем событие об отключении уведомлений на все устройства пользователя
		Gateway_Bus_Sender::snoozedTimerChanged($user_id, $new_snoozed_until);
		Gateway_Bus_Sender::notificationsSnoozed($user_id, Domain_Notifications_Entity_UserNotification_Extra::USER_NOTIFICATION_IS_SNOOZED);

		// если уперлись в максимальное время отключения уведомлений
		if ($new_snoozed_until == $max_time_limit) {
			throw new cs_NotificationsSnoozeTimeLimitExceeded($max_time_limit);
		}

		return $new_snoozed_until;
	}

	/**
	 * Установить время для отключения уведомлений
	 *
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	protected static function _setSnoozedUntil(int $user_id, int $interval_minutes, int $time_at, int $max_time_limit):int {

		Gateway_Db_CompanyData_MemberNotificationList::beginTransaction();

		try {

			// получаем запись на обновление
			$user_notification = Gateway_Db_CompanyData_MemberNotificationList::getForUpdate($user_id);

			// обновляем и коммитим
			$new_snoozed_until = self::_updateSnooze($user_notification, $interval_minutes, $time_at, $max_time_limit);

			Gateway_Db_CompanyData_MemberNotificationList::commitTransaction();
		} catch (cs_UserNotificationNotFound) {

			// в случае если записи не нашлось
			Gateway_Db_CompanyData_MemberNotificationList::rollback();

			// создаем запись и обновляем
			$user_notification = Domain_User_Action_Notifications_AddDevice::do($user_id, getDeviceId());
			$new_snoozed_until = self::_updateSnooze($user_notification, $interval_minutes, $time_at, $max_time_limit);
		}

		return $new_snoozed_until;
	}

	/**
	 * обновляем запись для отключения уведомлений
	 */
	protected static function _updateSnooze(Struct_Db_CompanyData_MemberNotification $user_notification, int $interval_minutes, int $time_at, int $max_time_limit):int {

		// получаем время включения уведомлений компании
		$new_snoozed_until = Domain_Notifications_Entity_UserNotification_Main::makeSnoozedUntil(
			$time_at, $max_time_limit, $user_notification->snoozed_until, $interval_minutes
		);

		// устанавливаем флаг для отключения уведомлений
		if ($interval_minutes == 0) {
			$user_notification->extra = Domain_Notifications_Entity_UserNotification_Extra::setSnoozed($user_notification->extra);
		} else {
			Gateway_Bus_CollectorAgent::init()->inc("row67"); // количество вызовов legacy
		}

		Gateway_Db_CompanyData_MemberNotificationList::set($user_notification->user_id, [
			"snoozed_until" => $new_snoozed_until,
			"updated_at"    => time(),
			"extra"         => $user_notification->extra,
		]);

		return $new_snoozed_until;
	}
}