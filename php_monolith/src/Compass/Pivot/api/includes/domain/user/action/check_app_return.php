<?php

namespace Compass\Pivot;

/**
 * Действие проверки возврата пользователя в приложение
 */
class Domain_User_Action_CheckAppReturn {

	/**
	 * выполняем
	 */
	public static function do(Struct_Db_PivotUser_User $user_info):Struct_Db_PivotUser_User {

		// если последняя активность пользователя совпадает с текущим днём, то ничего не делаем
		if ($user_info->last_active_day_start_at === dayStart()) {
			return $user_info;
		}

		$user_info->last_active_day_start_at = dayStart();

		// иначе обновляем время последней активности на текущий день
		$set = [
			"last_active_day_start_at" => $user_info->last_active_day_start_at,
			"updated_at"               => time(),
		];
		Gateway_Db_PivotUser_UserList::set($user_info->user_id, $set);

		// сбрасываем пивот-кэш для пользователя
		Gateway_Bus_PivotCache::clearUserCacheInfo($user_info->user_id);

		return $user_info;
	}
}