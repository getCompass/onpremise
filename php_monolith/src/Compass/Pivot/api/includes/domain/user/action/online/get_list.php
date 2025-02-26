<?php

namespace Compass\Pivot;

/**
 * Действие получения списка онлайна пользователей
 *
 * Class Domain_User_Action_Online_GetList
 */
class Domain_User_Action_Online_GetList {

	/**
	 * получаем списка онлайна пользователя
	 *
	 * @param array $user_id_list
	 *
	 * @return array
	 */
	public static function do(array $user_id_list):array {

		return Gateway_Bus_Activity::getUserOnlineList($user_id_list);
	}
}