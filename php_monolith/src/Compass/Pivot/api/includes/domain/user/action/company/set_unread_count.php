<?php

namespace Compass\Pivot;

/**
 * Установить количество непрочитанных сообщений
 *
 */
class Domain_User_Action_Company_SetUnreadCount {

	/**
	 * Установить количество непрочитанных сообщений
	 *
	 * @throws \queryException
	 * @throws \returnException
	 */
	public static function do(int $user_id, int $company_id, int $messages_unread_count, int $inbox_unread_count):void {

		try {

			Gateway_Db_PivotUser_Main::beginTransaction($user_id);

			Gateway_Db_PivotUser_CompanyInbox::getForUpdate($user_id, $company_id);

			Gateway_Db_PivotUser_CompanyInbox::set($user_id, $company_id, ["messages_unread_count_alias" => $messages_unread_count, "inbox_unread_count" => $inbox_unread_count]);

			Gateway_Db_PivotUser_Main::commitTransaction($user_id);
		} catch (\cs_RowIsEmpty) {

			Gateway_Db_PivotUser_Main::rollback($user_id);

			$dynamic_row = new Struct_Db_PivotUser_CompanyInbox(
				$user_id,
				$company_id,
				$messages_unread_count,
				$inbox_unread_count,
				time(),
				0
			);
			Gateway_Db_PivotUser_CompanyInbox::insert($dynamic_row);
		}
	}
}
