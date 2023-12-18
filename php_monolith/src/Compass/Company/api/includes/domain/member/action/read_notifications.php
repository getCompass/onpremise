<?php

namespace Compass\Company;

/**
 * Прочитываем уведомления
 */
class Domain_Member_Action_ReadNotifications {

	/**
	 * Выполняем
	 */
	public static function do(int $user_id, array $type_list):void {

		$type_int_list = [];
		foreach ($type_list as $type) {

			if (!array_key_exists($type, Domain_Member_Entity_Menu::CLIENT_TYPE_LIST_SCHEMA)) {
				throw new Domain_Member_Exception_IncorrectMenuType("not valid notification type");
			}

			// пропускаем тип заявок - ибо их можно убрать только приняв/удалив заявку
			if ($type === "join_request_list") {
				continue;
			}

			$type_int_list[] = Domain_Member_Entity_Menu::CLIENT_TYPE_LIST_SCHEMA[$type];
		}

		// прочитываем все непрочитанные уведомления
		$read_notifications_count = Domain_Member_Entity_Menu::readAllUnreadNotificationsByType($user_id, $type_int_list);

		// если были прочитанные уведомления
		if ($read_notifications_count > 0) {

			$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id);
			Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, $user_id, [], $extra);
		}

		// шлем событие администратору о прочтении уведомлений
		Gateway_Bus_Sender::memberMenuReadNotifications($user_id, $type_list);
	}
}