<?php

namespace Compass\Thread;

/**
 *
 * Получить данные user_inbox для пользователя
 */
class Domain_Thread_Action_GetUserInbox {

	/**
	 * Получить количество непрочитанных тредов и сообщений
	 *
	 */
	public static function do(int $user_id):array {

		// получаем количество непрочитанных тредов
		$dynamic_row = Gateway_Db_CompanyThread_UserInbox::getOne($user_id);

		if (!isset($dynamic_row["user_id"])) {
			return $dynamic_row;
		}

		// !!! - если количество непрочитанных меньше нуля
		if ($dynamic_row["thread_unread_count"] < 0 || $dynamic_row["message_unread_count"] < 0) {

			// отправляем задачу на актуализацию непрочитанных
			Type_Phphooker_Main::updateUserInboxForUnreadLessZero($user_id);

			// !!! - дальше выдаём его как 0
			$thread_unread_count                 = (int) $dynamic_row["thread_unread_count"];
			$message_unread_count                = (int) $dynamic_row["message_unread_count"];
			$dynamic_row["thread_unread_count"]  = $thread_unread_count < 0 ? 0 : $thread_unread_count;
			$dynamic_row["message_unread_count"] = $message_unread_count < 0 ? 0 : $message_unread_count;
		}

		return $dynamic_row;
	}
}