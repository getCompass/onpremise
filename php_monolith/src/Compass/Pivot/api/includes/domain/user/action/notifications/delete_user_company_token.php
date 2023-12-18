<?php

namespace Compass\Pivot;

/**
 * Удалить токен компании у устройства
 * Class Domain_User_Action_Notifications_DeleteUserCompanyToken
 */
class Domain_User_Action_Notifications_DeleteUserCompanyToken {

	/**
	 * Удалить токен компании у устройства
	 *
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(int $user_id, string $device_id, string $token, int $company_id):void {

		Type_User_Notifications::deleteUserCompanyPushNotificationToken($device_id, $user_id, $token, $company_id);
	}
}