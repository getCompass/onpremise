<?php

namespace Compass\Pivot;

/**
 * Снимаем отключение уведомления с таймером для указанного события
 */
class Domain_User_Action_Notifications_UnsetSnoozedEvent {

	/**
	 * Снимаем отключение уведомления с таймером для указанного события
	 *
	 * @throws cs_IncorrectNotificationToggleData
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(int $user_id, int $event, bool $is_snoozed):void {

		switch ($event) {

			case EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION:

				$event_mask = EVENT_TYPE_BELONGS_TO_GROUP_CONVERSATION_MASK;
				break;
			default:
				throw new cs_IncorrectNotificationToggleData("incorrect event to snooze");
		}

		// фиксируем ивент как замьюченный
		Type_User_Notifications::snoozeForEvent($user_id, $event_mask, $is_snoozed);

		Gateway_Bus_SenderBalancer::notificationsEventUnsnoozed($user_id, $event);
	}
}