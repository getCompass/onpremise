<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\ParamException;

/**
 * Добавить устройство в запись уведомлений пользователя
 */
class Domain_User_Action_Notifications_AddDevice {

	/**
	 * Добавляет пользователю новое авторизованное устройство.
	 *
	 * @throws \busException
	 * @throws paramException
	 * @throws \parseException
	 * @throws \queryException
	 * @throws \returnException
	 * @long Длинный запрос
	 */
	public static function do(int $user_id, string $device_id):Struct_Db_CompanyData_MemberNotification {

		try {

			$notification = Gateway_Db_CompanyData_MemberNotificationList::getOne($user_id);

			// если такой девайс уже числится за пользователем
			if (in_array($device_id, $notification->device_list, true)) {

				// в пивоте устанавливаем компанейский токен для устройства
				Gateway_Socket_Pivot::setUserCompanyToken($user_id, $device_id, $notification->token);
				return $notification;
			}

			$notification->device_list[] = $device_id;
			Gateway_Db_CompanyData_MemberNotificationList::set($user_id, [
				"device_list" => $notification->device_list,
			]);
		} catch (cs_UserNotificationNotFound) {

			$notification = new Struct_Db_CompanyData_MemberNotification(
				$user_id,
				0,
				time(),
				0,
				generateUUID(),
				[$device_id],
				Domain_Notifications_Entity_UserNotification_Extra::initExtra()
			);

			Gateway_Db_CompanyData_MemberNotificationList::insert($notification);
		}

		// в пивоте устанавливаем компанейский токен для устройства
		Gateway_Socket_Pivot::setUserCompanyToken($user_id, $device_id, $notification->token);

		// сбрасываем кэш в микросервисе для пользователя
		Gateway_Bus_Sender::clearUserNotificationCache($user_id);

		return $notification;
	}
}