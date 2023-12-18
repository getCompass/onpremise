<?php

namespace Compass\Pivot;

/**
 * класс для работы с токеном для пушей
 */
class Domain_User_Entity_UserCompanyPushToken {

	/**
	 * Добавить токен для пушей в таблицу с токенами
	 *
	 */
	public static function add(int $user_id, int $npc_type, int $company_id, string $company_push_token):void {

		// если не человек, то пуши для него не нужны
		if (!Type_User_Main::isHuman($npc_type)) {
			return;
		}

		// генерируем токен
		$token = new Struct_Db_PivotUser_NotificationCompanyPushToken($user_id, $company_id, $company_push_token, time(), 0);

		// пишем токен в пивот
		Gateway_Db_PivotUser_NotificationCompanyPushToken::insertOrUpdate($token);
	}
}