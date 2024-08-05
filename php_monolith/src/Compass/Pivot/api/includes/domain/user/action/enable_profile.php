<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Request\ParamException;

/**
 * Действие разблокирует пользователя в системе
 */
class Domain_User_Action_EnableProfile {

	/**
	 * Разблокируем пользователя в системе.
	 *
	 * @throws \cs_RowIsEmpty
	 * @throws \paramException
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function do(int $user_id):void {

		/** начало транзакции **/
		Gateway_Db_PivotUser_UserList::beginTransaction($user_id);

		Type_System_Admin::log("user-enabled", "разблочиваем профиль пользователя {$user_id}");

		// блокируем запись
		try {

			$user_info = Gateway_Db_PivotUser_UserList::getForUpdate($user_id);
		} catch (\Exception $ex) {

			Gateway_Db_PivotUser_UserList::rollback($user_id);
			throw $ex;
		}

		// если пользователь уже разблокирован, то дальше не идем
		if (!Type_User_Main::isDisabledProfile($user_info->extra)) {

			Gateway_Db_PivotUser_UserList::rollback($user_id);
			throw new ParamException("user is already unblocked");
		}

		$set = [
			"extra"      => Type_User_Main::setProfileEnabled($user_info->extra),
			"updated_at" => time(),
		];

		// обновляем данные учетной записи
		Gateway_Db_PivotUser_UserList::set($user_info->user_id, $set);

		Gateway_Db_PivotUser_UserList::commitTransaction($user_id);
		/** конец транзакции **/

		// не забываем сбросить кэш
		Gateway_Bus_PivotCache::clearUserCacheInfo($user_id);
	}
}
