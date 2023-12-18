<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Удалить устройство из записи уведомлений пользователя
 */
class Domain_User_Action_Notifications_DeleteDevice {

	/**
	 *
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $user_id, string $device_id):void {

		try {
			$notification = Gateway_Db_CompanyData_MemberNotificationList::getOne($user_id);
		} catch (cs_UserNotificationNotFound) {
			return;
		}

		// ищем id девайса в массиве, и удаляем, если его нет
		$key = array_search($device_id, $notification->device_list);
		if ($key !== false) {
			unset($notification->device_list[$key]);
		}

		Gateway_Db_CompanyData_MemberNotificationList::set($user_id, [
			"device_list" => array_values($notification->device_list),
		]);

		// сбрасываем кэш в микросервисе для пользователя
		Gateway_Bus_Sender::clearUserNotificationCache($user_id);

		// удаляем токен из пивота
		Gateway_Socket_Pivot::setUserCompanyToken($user_id, $device_id, $notification->token, false);
	}
}