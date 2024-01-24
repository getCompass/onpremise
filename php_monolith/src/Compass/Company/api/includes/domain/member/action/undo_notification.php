<?php

namespace Compass\Company;

/**
 * Убираем уведомление
 */
class Domain_Member_Action_UndoNotification {

	/**
	 * Выполняем
	 */
	public static function do(int $action_user_id, int $type, int $hiring_request_id = 0):void {

		// валидируем параметры
		Domain_User_Entity_Validator::assertValidUserId($action_user_id);
		Domain_Member_Entity_Validator::assertValidMenuType($type);

		// если уведомление предназначено для определенной группы - отправляем только ей
		$owner_list = match (isset(Domain_Member_Entity_Menu::NOTIFICATION_PERMISSION_REQUIREMENTS[$type])) {

			true    => Domain_User_Action_Member_GetByPermissions::do([Domain_Member_Entity_Menu::NOTIFICATION_PERMISSION_REQUIREMENTS[$type]]),
			default => Domain_User_Action_Member_GetUserRoleList::do([\CompassApp\Domain\Member\Entity\Member::ROLE_ADMINISTRATOR]),
		};

		$owner_id_list = array_column($owner_list, "user_id");

		// получаем все связанные уведомления с пользователем
		$notification_list    = Domain_Member_Entity_Menu::getUnreadNotificationList($owner_id_list, $action_user_id, $type);
		$unread_owner_id_list = array_column($notification_list, "user_id");

		// прочитываем уведомления
		Domain_Member_Entity_Menu::readNotificationsByType($unread_owner_id_list, $action_user_id, $type);

		// обновляем бадж для администраторов
		foreach ($unread_owner_id_list as $user_id) {

			$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id);
			Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, $user_id, [], $extra);
		}

		$data = [];
		if ($type == Domain_Member_Entity_Menu::JOIN_REQUEST) {
			$data = ["join_request_id" => $hiring_request_id];
		}

		// шлем событие всем администраторам об отмененном уведомлении
		Gateway_Bus_Sender::memberMenuUndoNotification($action_user_id, $type, $data);
	}
}