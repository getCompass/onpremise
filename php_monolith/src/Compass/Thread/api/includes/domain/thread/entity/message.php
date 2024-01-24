<?php

namespace Compass\Thread;

/**
 * Класс для работы c сообщениями тредов.
 */
class Domain_Thread_Entity_Message {

	/**
	 * Определяет, видит ли пользователь сообщение или нет.
	 * В общем случае для тредов сообщение невидимо, только если пользователь скрыл его.
	 */
	public static function isInvisibleForUser(int $user_id, array $message):bool {

		return Type_Thread_Message_Main::getHandler($message)::isMessageHiddenForUser($message, $user_id);
	}
}
