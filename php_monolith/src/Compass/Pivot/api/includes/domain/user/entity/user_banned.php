<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с баном пользователя
 */
class Domain_User_Entity_UserBanned {

	/**
	 * Получаем запись с баном пользователя
	 *
	 * @param int $user_id
	 *
	 * @return Struct_Db_PivotUser_UserBanned
	 * @throws ParseFatalException
	 * @throws RowNotFoundException
	 */
	public static function get(int $user_id):Struct_Db_PivotUser_UserBanned {

		return Gateway_Db_PivotUser_UserBanned::getOne($user_id);
	}
}