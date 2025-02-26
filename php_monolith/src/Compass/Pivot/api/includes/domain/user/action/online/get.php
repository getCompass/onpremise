<?php

namespace Compass\Pivot;

/**
 * Действие получения онлайна пользователя
 *
 * Class Domain_User_Action_Online_Get
 */
class Domain_User_Action_Online_Get {

	/**
	 * получаем онлайн пользователя
	 *
	 * @param int $user_id
	 *
	 * @return int
	 */
	public static function do(int $user_id):int {

		try {
			$user_activity = Gateway_Bus_Activity::getUserOnline($user_id);
		} catch (cs_UserNotFound) {

			// если записи нет, то отдаем что еще не был онлайн (равно 0)
			return 0;
		}

		return $user_activity->last_ws_ping_at;
	}
}