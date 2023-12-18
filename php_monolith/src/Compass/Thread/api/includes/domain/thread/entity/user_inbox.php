<?php

namespace Compass\Thread;

/**
 * Класс взаимодействия с записью непрочитанных в тредах
 */
class Domain_Thread_Entity_UserInbox {

	/**
	 * Получаем запись и проверяем, что количество непрочитанных тредов не превышает количество непрочитанных сообщений в тредах
	 */
	public static function getInboxForUpdateByUserId(int $user_id):array {

		$user_inbox_row = Gateway_Db_CompanyThread_UserInbox::getForUpdate($user_id);

		// количество непрочитанных тредов не должно превышать количество непрочитанных сообщений в тредах
		if ($user_inbox_row["message_unread_count"] < $user_inbox_row["thread_unread_count"]) {
			$user_inbox_row["thread_unread_count"] = $user_inbox_row["message_unread_count"];
		}

		return $user_inbox_row;
	}
}
