<?php

namespace Compass\Thread;

/**
 * класс с вспомогательными функциями, связанными с метой треда
 */
class Type_Thread_Meta_Utils {

	/**
	 * возвращает количество скрытых сообщений пользователя
	 *
	 */
	public static function getCountHiddenMessage(array $meta_row, int $user_id):int {

		return Type_Thread_Meta_Users::getCountHiddenMessage($meta_row["users"][$user_id]);
	}

	/**
	 * возвращает количество сообщений в треде
	 *
	 */
	public static function getMessageCount(array $meta_row):int {

		return $meta_row["message_count"];
	}

	/**
	 * пользователь скрыл все сообщения в треде?
	 *
	 */
	public static function isAllMessagesHidden(array $meta_row, int $user_id):bool {

		return self::getCountHiddenMessage($meta_row, $user_id) == self::getMessageCount($meta_row);
	}
}