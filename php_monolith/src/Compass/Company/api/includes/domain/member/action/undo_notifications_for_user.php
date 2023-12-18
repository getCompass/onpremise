<?php

namespace Compass\Company;

/**
 * Убираем все уведомления у пользователя
 */
class Domain_Member_Action_UndoNotificationsForUser {

	/**
	 * Выполняем
	 */
	public static function do(int $user_id):void {

		// валидируем параметры
		Domain_User_Entity_Validator::assertValidUserId($user_id);

		// прочитываем все уведомления пользователя
		$read_notifications_count = Domain_Member_Entity_Menu::readAllUnreadNotifications($user_id);

		// если были прочитанные уведомления
		if ($read_notifications_count > 0) {

			$extra = Gateway_Bus_Company_Timer::getExtraForUpdateBadge($user_id);
			Gateway_Bus_Company_Timer::setTimeout(Gateway_Bus_Company_Timer::UPDATE_BADGE, $user_id, [], $extra);
		}
	}
}