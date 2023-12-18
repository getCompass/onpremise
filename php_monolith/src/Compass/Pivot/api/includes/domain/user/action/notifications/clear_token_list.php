<?php

namespace Compass\Pivot;

/**
 * Очищаем все пуш токены пользователя
 */
class Domain_User_Action_Notifications_ClearTokenList {

	/**
	 *
	 * @throws \returnException
	 * @throws \queryException
	 */
	public static function do(int $user_id, array $device_id_list, int $company_id, string $token):void {

		$list = Gateway_Db_PivotData_DeviceList::getAllByDeviceIdList($device_id_list);

		// открепляем user-company-токены от девайсов пользователя для выбранной компании
		foreach ($list as $device_row) {

			// если девайс уже НЕ принадлежит нашему пользователю, то ничего не трогаем
			if ($device_row["user_id"] != $user_id) {
				continue;
			}

			Type_User_Notifications::deleteUserCompanyPushNotificationToken($device_row["device_id"], $user_id, $token, $company_id);
		}
	}
}