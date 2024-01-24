<?php

namespace Compass\Thread;

/**
 *
 * Получить количество непрочитанных тредов и сообщений
 */
class Domain_Thread_Action_GetTotalUnreadCount {

	/**
	 * Получить количество непрочитанных тредов и сообщений
	 *
	 */
	public static function do(int $user_id):array {

		// получаем количество непрочитанных тредов
		$dynamic_row = Domain_Thread_Action_GetUserInbox::do($user_id);

		if (!isset($dynamic_row["user_id"])) {

			return [
				"threads_unread_count"  => 0,
				"messages_unread_count" => 0,
			];
		}

		return [
			"threads_unread_count"  => (int) $dynamic_row["thread_unread_count"],
			"messages_unread_count" => (int) $dynamic_row["message_unread_count"],
		];
	}
}