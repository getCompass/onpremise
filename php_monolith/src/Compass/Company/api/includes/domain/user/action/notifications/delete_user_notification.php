<?php

namespace Compass\Company;

/**
 * Удаляем запись с токеном для пользователя в компании и отправляем задачу на пивот
 */
class Domain_User_Action_Notifications_DeleteUserNotification {

	/**
	 *
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $user_id):void {

		// если по какой то причине у нас нет записи о токенах пользователя в базе - завершаем выполнение функции
		try {
			$notification = Gateway_Db_CompanyData_MemberNotificationList::getOne($user_id);
		} catch (cs_UserNotificationNotFound) {
			return;
		}

		Gateway_Db_CompanyData_MemberNotificationList::deleteByUser($user_id);

		Gateway_Socket_Pivot::clearTokenList($user_id, $notification->device_list, $notification->token);
	}
}