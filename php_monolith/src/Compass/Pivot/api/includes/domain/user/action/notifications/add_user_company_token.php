<?php

namespace Compass\Pivot;

/**
 * Добавить токен компании к устройству
 */
class Domain_User_Action_Notifications_AddUserCompanyToken {

	/**
	 * Добавить токен компании к устройству
	 *
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(int $user_id, string $device_id, string $token, int $company_id):void {

		Type_User_Notifications::addUserCompanyPushNotificationToken($device_id, $user_id, $token, $company_id);
	}
}