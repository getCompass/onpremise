<?php

namespace Compass\Pivot;

/**
 * Выключаем уведомлления для определенного типа действий
 */
class Domain_User_Action_Notifications_DoDisableForEvent {

	public static function do(int $user_id, int $event_type):void {

		// отключаем уведомления в зависимости от типа
		self::_setNotificationStatusForEventType($event_type, $user_id, false);

		// отправляем событие notifications_disabled_for_event на все устройства пользователя
		Gateway_Bus_SenderBalancer::notificationsDisabledForEvent($user_id, $event_type);
	}

	/**
	 * включаем или отключаем уведомления для переданного типа
	 * если для подтипа приходит null, то ничего не меняем в подтипе
	 *
	 * @throws cs_IncorrectNotificationToggleData
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _setNotificationStatusForEventType(int $event_type, int $user_id, bool $status):bool {

		switch ($event_type) {

			// сообщения в любых диалогах
			case EVENT_TYPE_CONVERSATION_MESSAGE:

				Type_User_Notifications::setForEvent($user_id, EVENT_TYPE_CONVERSATION_MESSAGE_MASK, $status);
				break;

			// треды
			case EVENT_TYPE_THREAD_MESSAGE:

				Type_User_Notifications::setForEvent($user_id, EVENT_TYPE_THREAD_MESSAGE_MASK, $status);
				break;

			// инвайты
			case EVENT_TYPE_INVITE_MESSAGE:

				Type_User_Notifications::setForEvent($user_id, EVENT_TYPE_INVITE_MESSAGE_MASK, $status);
				break;

			default:
				throw new cs_IncorrectNotificationToggleData();
		}

		return true;
	}
}