<?php

namespace Compass\Company;

/**
 * Добавляем новое уведомление
 */
class Domain_Member_Action_AddNotification {

	/**
	 * Выполняем
	 * @long
	 */
	public static function do(int $action_user_id, int $type, Domain_Member_Entity_Notification_Extra $extra, int $exclude_receiver_id = 0):void {

		// валидируем параметры
		Domain_Member_Entity_Validator::assertValidMenuType($type);

		// если уведомление предназначено для определенной группы - отправляем только ей
		$owner_list = match (isset(Domain_Member_Entity_Menu::NOTIFICATION_PERMISSION_REQUIREMENTS[$type])) {

			true => Domain_User_Action_Member_GetByPermissions::do([Domain_Member_Entity_Menu::NOTIFICATION_PERMISSION_REQUIREMENTS[$type]]),
			default => Domain_User_Action_Member_GetUserRoleList::do([\CompassApp\Domain\Member\Entity\Member::ROLE_ADMINISTRATOR]),
		};

		$owner_id_list = array_column($owner_list, "user_id");

		// получаем все связанные уведомления с пользователем
		$notification_list       = Domain_Member_Entity_Menu::getNotificationList($owner_id_list, $action_user_id, $type);
		$found_owner_id_list     = array_column($notification_list, "user_id");
		$not_found_owner_id_list = array_diff($owner_id_list, $found_owner_id_list);

		// если не требуется добавлять уведомление
		if ($exclude_receiver_id > 0 && Domain_Member_Entity_Menu::isExcludeTypeNotification($type)) {

			$not_found_owner_id_list = array_diff($not_found_owner_id_list, [$exclude_receiver_id]);
			$found_owner_id_list     = array_diff($found_owner_id_list, [$exclude_receiver_id]);
		}

		// пишем/обновляем уведомление в базу для каждого администратора
		Domain_Member_Entity_Menu::createOrUpdate($not_found_owner_id_list, $found_owner_id_list, $action_user_id, $type);

		// обновляем бадж для администраторов
		foreach ($owner_id_list as $user_id) {

			$badge_extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id);
			Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, $user_id, [], $badge_extra);
		}

		$data = [];

		if ($type === Domain_Member_Entity_Menu::JOIN_REQUEST) {

			$data = [
				"join_request_id"             => $extra->getHiringRequestId(),
				"company_name"                => $extra->getCompanyName(),
				"action_user_full_name"       => $extra->getActionUserFullName(),
				"action_user_avatar_file_key" => $extra->getActionUserAvatarFileKey(),
				"action_user_avatar_color"    => $extra->getActionUserAvatarColor(),
			];
		}

		if (in_array($type, [Domain_Member_Entity_Menu::ACTIVE_MEMBER, Domain_Member_Entity_Menu::GUEST_MEMBER])) {

			$data = [
				"company_name"                => $extra->getCompanyName(),
				"action_user_full_name"       => $extra->getActionUserFullName(),
				"action_user_avatar_file_key" => $extra->getActionUserAvatarFileKey(),
				"action_user_avatar_color"    => $extra->getActionUserAvatarColor(),
			];
		}

		$push_data = match ($type) {
			Domain_Member_Entity_Menu::JOIN_REQUEST => Domain_Space_Entity_Push::makeJoinRequestPushData($data["action_user_full_name"], $data["company_name"]),
			Domain_Member_Entity_Menu::ACTIVE_MEMBER => Domain_Space_Entity_Push::makeActiveMemberPushData($data["action_user_full_name"], $data["company_name"]),
			Domain_Member_Entity_Menu::GUEST_MEMBER => Domain_Space_Entity_Push::makeGuestMemberPushData($data["action_user_full_name"], $data["company_name"]),
			default => [],
		};

		// шлем событие всем администраторам о новом уведомлении
		Gateway_Bus_Sender::memberMenuNewNotification($action_user_id, $type, $exclude_receiver_id, $data, $push_data);
	}
}