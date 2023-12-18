<?php

namespace Compass\Pivot;

/**
 * Включаем уведомления для определенного вида событий
 */
class Domain_User_Action_Notifications_DoEnableForEvent {

	/**
	 * Выключаем уведомлления для определенного типа действий
	 *
	 * @throws cs_IncorrectNotificationToggleData
	 * @throws \parseException|\returnException
	 */
	public static function do(int $user_id, int $event_type):array {

		self::_setNotificationStatusForEventType($user_id, $event_type, true);

		Gateway_Bus_SenderBalancer::notificationsEnabledForEvent($user_id, $event_type);

		return [];
	}

	// включаем или отключаем уведомления для переданного типа
	// если для подтипа приходит null, то ничего не меняем в подтипе
	// @long
	/**
	 *
	 * @throws cs_IncorrectNotificationToggleData
	 * @throws \parseException
	 * @throws \returnException
	 */
	protected static function _setNotificationStatusForEventType(int $user_id, int $event_type, bool $status):bool {

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
				throw new cs_IncorrectNotificationToggleData("incorrect event type");
		}
		return true;
	}
}