<?php

namespace Compass\Thread;

/**
 * Прочитать тред
 */
class Domain_Thread_Action_DoRead {

	/**
	 * Прочитать тред
	 *
	 */
	public static function do(int $user_id, string $thread_map, string $last_read_message_map):bool {

		$was_unread = false;

		// получаем запись из thread_menu
		Gateway_Db_CompanyThread_Main::beginTransaction();
		$thread_menu_row = Gateway_Db_CompanyThread_UserThreadMenu::getForUpdate($user_id, $thread_map);

		// сработает в том случае если пользователь может читать тред, но не подписан на него
		if (!isset($thread_menu_row["user_id"])) {

			Gateway_Db_CompanyThread_Main::rollback();
			return $was_unread;
		}

		// если сообщение уже прочитано - отменяем транзакцию
		$need_read_message_index    = \CompassApp\Pack\Message\Thread::getThreadMessageIndex($last_read_message_map);
		$current_read_message_index = self::_getCurrentReadThreadMessageIndex($thread_menu_row);
		if ($need_read_message_index <= $current_read_message_index && $thread_menu_row["unread_count"] == 0) {

			Gateway_Db_CompanyThread_Main::rollback();
			return $was_unread;
		}

		$was_unread = true;

		// если есть непрочитанные сообщения, обновляем данные юзера
		self::_updateUserDataOnMessageRead($user_id, $thread_map, $thread_menu_row, $last_read_message_map);
		Gateway_Db_CompanyThread_Main::commitTransaction();

		return $was_unread;
	}

	/**
	 * Получаем индекс последнего прочитанного сообщения
	 *
	 */
	protected static function _getCurrentReadThreadMessageIndex(array $thread_menu_row):int {

		// если в thread_menu есть последнее прочитанное сообщение - получаем его индекс
		$current_read_message_index = 0;
		if (mb_strlen($thread_menu_row["last_read_message_map"]) > 0) {
			$current_read_message_index = \CompassApp\Pack\Message\Thread::getThreadMessageIndex($thread_menu_row["last_read_message_map"]);
		}
		return $current_read_message_index;
	}

	/**
	 * Если есть непрочитанные сообщения, обновляем данные юзера
	 *
	 */
	protected static function _updateUserDataOnMessageRead(int $user_id, string $thread_map, array $thread_menu_row, string $last_read_message_map):void {

		$set = [];

		$unread_count = $thread_menu_row["unread_count"];

		// если есть непрочитанные
		if ($unread_count > 0) {

			$set["unread_count"] = 0;

			// получаем количество непрочитанных для пользователя
			$user_inbox_row = Domain_Thread_Entity_UserInbox::getInboxForUpdateByUserId($user_id);

			$user_inbox_set = [
				"message_unread_count" => $user_inbox_row["message_unread_count"] - $unread_count,
				"thread_unread_count"  => $user_inbox_row["thread_unread_count"] - 1,
				"updated_at"           => time(),
			];

			// обновляем общее количество непрочитанных сообщений
			Gateway_Db_CompanyThread_UserInbox::set($user_id, $user_inbox_set);
		}

		// обновляем запись в thread_menu
		$set["last_read_message_map"] = $last_read_message_map;
		Gateway_Db_CompanyThread_UserThreadMenu::set($user_id, $thread_map, $set);
	}
}
