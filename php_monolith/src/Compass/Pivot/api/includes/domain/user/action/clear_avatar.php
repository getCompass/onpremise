<?php

namespace Compass\Pivot;

/**
 * Действие очистки аватара в профиле пользователя
 *
 * Class Domain_User_Action_ClearAvatar
 */
class Domain_User_Action_ClearAvatar {

	/**
	 * Очищаем аватар
	 *
	 * @throws \parseException
	 */
	public static function do(int $user_id):void {

		// очищаем аватар
		$set = [
			"avatar_file_map" => "",
			"updated_at"      => time(),
		];
		Gateway_Db_PivotUser_UserList::set($user_id, $set);

		// очищаем кеш пользователя на pivot
		Gateway_Bus_PivotCache::clearUserCacheInfo($user_id);

		// отправляем задачу на очистку кеша пользователя в компаниях
		Type_Phphooker_Main::onUserInfoChange($user_id);

		Gateway_Bus_SenderBalancer::avatarCleared($user_id);
	}
}