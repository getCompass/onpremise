<?php

namespace Compass\Pivot;

/**
 * Получить список девайсов пользователя
 */
class Domain_User_Action_Notifications_GetDeviceList {

	/**
	 * Получить список девайсов пользователя
	 *
	 */
	public static function do(int $user_id):array {

		try {
			$user_notification = Gateway_Db_PivotUser_NotificationList::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			return [];
		}

		return Gateway_Db_PivotData_DeviceList::getAllByDeviceIdList($user_notification->device_list);
	}
}