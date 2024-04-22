<?php

namespace Compass\Premise;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;
use BaseFrame\Exception\Gateway\DBShardingNotFoundException;

/**
 * Проверка, что пользователь существует
 */
class Domain_User_Action_AssertUserExists {

	/**
	 * Выполняем
	 *
	 * @param int $premise_user_id
	 *
	 * @return void
	 * @throws Domain_User_Exception_IsDisabled
	 * @throws Domain_User_Exception_NotFound
	 * @throws ParseFatalException
	 */
	public static function do(int $premise_user_id):void {

		// проверяем, что пользователь действительно удален
		try {
			$user = Gateway_Db_PivotUser_UserList::getOne($premise_user_id);
		} catch (RowNotFoundException|DBShardingNotFoundException) {
			throw new Domain_User_Exception_NotFound("we did not find the user");
		}

		// проверяем, вдруг пользователь удален
		if (Type_User_Main::isDisabledProfile($user->extra)) {
			throw new Domain_User_Exception_IsDisabled("user is deleted");
		}

		// если по какой-то причине пользователь есть в базе, и не удален
		// при этом есть в базе премайза
		// орем, что все плохо
		throw new ParseFatalException("user not found");
	}
}