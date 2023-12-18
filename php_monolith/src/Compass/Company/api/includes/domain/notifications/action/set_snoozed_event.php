<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Устанавливаем отключение уведомления с таймером для указанного события
 */
class Domain_Notifications_Action_SetSnoozedEvent {

	/**
	 * Устанавливаем отключение уведомления с таймером для указанного события
	 *
	 * @throws \busException
	 * @throws cs_IncorrectNotificationToggleData
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(int $user_id, int $event, bool $is_snoozed):void {

		$event_mask = match ($event) {

			EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION => EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION_MASK,
			default                                  => throw new cs_IncorrectNotificationToggleData("incorrect event to snooze"),
		};

		self::_setSnoozedForEvent($user_id, $event_mask, $is_snoozed);

		// сбрасываем кэш в микросервисе для пользователя
		Gateway_Bus_Sender::clearUserNotificationCache($user_id);

		if ($is_snoozed) {

			Gateway_Bus_Sender::notificationsEventSnoozed($user_id, $event);
			return;
		}

		Gateway_Bus_Sender::notificationsEventUnsnoozed($user_id, $event);
	}

	/**
	 * Отключить уведомления для события
	 *
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	protected static function _setSnoozedForEvent(int $user_id, int $event_mask, int $is_snoozed):void {

		Gateway_Db_CompanyData_MemberNotificationList::beginTransaction();

		// получаем запись на обновление
		try {

			$user_notification = Gateway_Db_CompanyData_MemberNotificationList::getForUpdate($user_id);

			Gateway_Db_CompanyData_MemberNotificationList::set($user_notification->user_id, [

				"extra"      => Domain_Notifications_Entity_UserNotification_Extra::snoozeForEvent($user_notification->extra, $event_mask, $is_snoozed),
				"updated_at" => time(),
			]);

			Gateway_Db_CompanyData_MemberNotificationList::commitTransaction();
		} catch (cs_UserNotificationNotFound) {

			Gateway_Db_CompanyData_MemberNotificationList::rollback();
			$user_notification = Domain_User_Action_Notifications_AddDevice::do($user_id, getDeviceId());

			Gateway_Db_CompanyData_MemberNotificationList::set($user_notification->user_id, [

				"extra"      => Domain_Notifications_Entity_UserNotification_Extra::snoozeForEvent($user_notification->extra, $event_mask, $is_snoozed),
				"updated_at" => time(),
			]);
		}
	}
}