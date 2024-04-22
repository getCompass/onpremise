<?php

namespace Compass\Premise;

/**
 * Класс для действия - обновить права у пользователя
 */
class Domain_User_Action_UpdatePermissions {

	/**
	 * Выполняем
	 *
	 * @param int $user_id
	 * @param int $permissions
	 *
	 * @return void
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function do(int $user_id, int $permissions):void {

		$set = [
			"premise_permissions"     => $permissions,
			"has_premise_permissions" => $permissions == Domain_User_Entity_Permissions::DEFAULT ? 0 : 1,
		];
		Gateway_Db_PremiseUser_UserList::set($user_id, $set);
	}
}