<?php

namespace Compass\Pivot;

/**
 * Действие для блокировки пользователя
 */
class Domain_User_Action_DisableProfile {

	/**
	 * выполняем
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws cs_UserAlreadyBlocked
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $user_id):Struct_Db_PivotUser_User {

		/** начало транзакции **/
		Gateway_Db_PivotUser_UserList::beginTransaction($user_id);

		Type_System_Admin::log("user-kicker", "отключаю профиль пользователя {$user_id}");

		// блокируем запись
		try {

			$user_info = Gateway_Db_PivotUser_UserList::getForUpdate($user_id);
		} catch (\Exception $ex) {

			Gateway_Db_PivotUser_UserList::rollback($user_id);
			throw $ex;
		}

		// если пользователь уже заблокирован, то дальше блокировать нет нужды
		if (Type_User_Main::isDisabledProfile($user_info->extra)) {

			Gateway_Db_PivotUser_UserList::rollback($user_id);
			throw new cs_UserAlreadyBlocked("user is already blocked");
		}

		$user_info->extra = Type_User_Main::setProfileDisabled($user_info->extra, time());
		$set              = [
			"extra"      => $user_info->extra,
			"updated_at" => time(),
		];

		// обновляем данные о блокировке учетной записи
		Gateway_Db_PivotUser_UserList::set($user_info->user_id, $set);

		Gateway_Db_PivotUser_UserList::commitTransaction($user_id);
		/** конец транзакции **/

		// не забываем сбросить кэш
		Gateway_Bus_PivotCache::clearUserCacheInfo($user_id);

		return $user_info;
	}
}
