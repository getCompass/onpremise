<?php

namespace Compass\Thread;

/**
 * класс для работы с dynamic таблицей
 */
class Type_UserThread_Dynamic {

	// получаем запись из dynamic, создаем если её не было
	public static function getForceExist(int $user_id):array {

		// получаем запись
		$user_dynamic_row = Domain_Thread_Action_GetUserInbox::do($user_id);

		// если не оказалось - создаем
		if (!isset($user_dynamic_row["user_id"])) {
			$user_dynamic_row = Gateway_Db_CompanyThread_UserInbox::insert($user_id);
		}

		return $user_dynamic_row;
	}
}