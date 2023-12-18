<?php

namespace Compass\Thread;

/**
 * Установить тред как непрочитанный
 */
class Domain_Thread_Action_SetAsUnread {

	/**
	 * Установить тред как непрочитанный
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function do(int $user_id, string $thread_map, string $previous_message_map):void {

		// получаем запись из thread_menu, блокировка чтобы не допустить рассинхрона unread_count
		Gateway_Db_CompanyThread_Main::beginTransaction();
		$thread_menu_row = Gateway_Db_CompanyThread_UserThreadMenu::getForUpdate($user_id, $thread_map);

		if (!isset($thread_menu_row["user_id"])) {

			Gateway_Db_CompanyThread_Main::rollback();
			throw new \cs_RowIsEmpty("not found thread menu for user");
		}

		// если тред уже не прочитан, то просто выходим
		if ($thread_menu_row["unread_count"] != 0) {

			Gateway_Db_CompanyThread_Main::rollback();
			return;
		}

		$set = [
			"unread_count"          => 1,
			"last_read_message_map" => $previous_message_map,
			"updated_at"            => time(),
		];

		// получаем количество непрочитанных для пользователя
		$user_inbox_row = Domain_Thread_Entity_UserInbox::getInboxForUpdateByUserId($user_id);

		Gateway_Db_CompanyThread_UserInbox::set($user_id, [
			"message_unread_count" => $user_inbox_row["message_unread_count"] + 1,
			"thread_unread_count"  => $user_inbox_row["thread_unread_count"] + 1,
			"updated_at"           => time(),
		]);

		Gateway_Db_CompanyThread_UserThreadMenu::set($user_id, $thread_map, $set);
		Gateway_Db_CompanyThread_Main::commitTransaction();
	}
}