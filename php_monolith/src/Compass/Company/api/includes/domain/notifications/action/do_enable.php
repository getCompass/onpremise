<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Базовый класс для действия для включения уведомлений
 */
class Domain_Notifications_Action_DoEnable {

	/**
	 * Выполняем action
	 *
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(int $user_id):void {

		Gateway_Db_CompanyData_MemberNotificationList::beginTransaction();

		// получаем запись на обновление
		try {

			$user_notification        = Gateway_Db_CompanyData_MemberNotificationList::getForUpdate($user_id);
			$user_notification->extra = Domain_Notifications_Entity_UserNotification_Extra::unsnoozeAllEvents($user_notification->extra);
			$user_notification->extra = Domain_Notifications_Entity_UserNotification_Extra::setNotSnoozed($user_notification->extra);

			Gateway_Db_CompanyData_MemberNotificationList::set($user_notification->user_id, [
				"snoozed_until" => 0,
				"updated_at"    => time(),
				"extra"         => $user_notification->extra,
			]);

			Gateway_Db_CompanyData_MemberNotificationList::commitTransaction();
		} catch (cs_UserNotificationNotFound) {

			Gateway_Db_CompanyData_MemberNotificationList::rollback();
			$user_notification = Domain_User_Action_Notifications_AddDevice::do($user_id, getDeviceId());
		}

		// сбрасываем кэш в микросервисе для пользователя
		Gateway_Bus_Sender::clearUserNotificationCache($user_id);

		// отправляем событие о включении уведомлений на все устройства пользователя
		Gateway_Bus_Sender::snoozedTimerChanged($user_notification->user_id, 0);
		Gateway_Bus_Sender::notificationsSnoozed($user_notification->user_id, Domain_Notifications_Entity_UserNotification_Extra::USER_NOTIFICATION_IS_NOT_SNOOZED);
	}
}