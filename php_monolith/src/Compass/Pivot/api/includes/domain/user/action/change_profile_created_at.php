<?php

namespace Compass\Pivot;

/**
 * Действие для изменения времени создания профиля пользователя
 */
class Domain_User_Action_ChangeProfileCreatedAt {

	/**
	 * Изменяем время создания профиля пользователя
	 *
	 * @param int $user_id
	 * @param int $profile_created_at
	 *
	 * @throws \parseException
	 */
	public static function do(int $user_id, int $profile_created_at):void {

		// задаем время
		$set = [
			"created_at" => $profile_created_at,
		];
		Gateway_Db_PivotUser_UserList::set($user_id, $set);

		// очищаем кеш пользователя на pivot
		Gateway_Bus_PivotCache::clearUserCacheInfo($user_id);
	}
}