<?php

namespace Compass\Conversation;

use AnalyticUtils\Domain\Counter\Entity\Partner;
use AnalyticUtils\Domain\Counter\Entity\User as UserCounter;

/**
 * Экшен для прочтения одного непрочитанного сообщения диалога
 */
class Domain_Conversation_Feed_Action_ReadMessage {

	/**
	 * Читаем одно непрочитанное сообщение диалога
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 * @param string $message_map
	 *
	 * @return array
	 * @throws Domain_Conversation_Exception_Message_IsNotRead
	 * @throws Domain_Conversation_Exception_User_IsNotMember
	 * @throws \cs_UnpackHasFailed
	 * @throws \returnException
	 */
	public static function run(int $user_id, string $conversation_map, string $message_map):array {

		Gateway_Db_CompanyConversation_Main::beginTransaction();

		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getForUpdate($user_id, $conversation_map);

		// если пользователь не состоит в диалоге
		if (!isset($left_menu_row["user_id"]) || $left_menu_row["is_leaved"] == 1) {

			Gateway_Db_CompanyConversation_Main::rollback();
			throw new Domain_Conversation_Exception_User_IsNotMember("User is not conversation member");
		}

		// если в диалоге unread_count не равен 1
		if ($left_menu_row["unread_count"] != 1) {

			Gateway_Db_CompanyConversation_Main::rollback();
			throw new Domain_Conversation_Exception_Message_IsNotRead("Conversation message is not read");
		}

		$need_read_message_index = \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($message_map);
		$last_read_message_index = self::_getLastReadMessageIndex($left_menu_row);

		// если индекс пришедшего сообщения меньше либо равно индексу текущего прочитанного, то выходим
		if ($need_read_message_index <= $last_read_message_index) {

			Gateway_Db_CompanyConversation_Main::rollback();
			throw new Domain_Conversation_Exception_Message_IsNotRead("Conversation message is not read");
		}

		// читаем сообщение
		$left_menu_row = self::setConversationAsRead($user_id, $message_map, $left_menu_row);

		Gateway_Db_CompanyConversation_Main::commitTransaction();
		return $left_menu_row;
	}

	/**
	 * Получаем индекс последнего прочитанного сообщения
	 *
	 * @param array $left_menu_row
	 *
	 * @return int
	 * @throws \cs_UnpackHasFailed
	 */
	protected static function _getLastReadMessageIndex(array $left_menu_row):int {

		$message_index = 0;
		if (mb_strlen($left_menu_row["last_read_message_map"]) > 0) {
			$message_index = \CompassApp\Pack\Message\Conversation::getConversationMessageIndex($left_menu_row["last_read_message_map"]);
		}

		return $message_index;
	}

	/**
	 * Установить значение непрочитанного левого меню
	 *
	 * @long Много параметров для установки
	 *
	 * @param int    $user_id
	 * @param string $need_read_message_map
	 * @param array  $left_menu_row
	 *
	 * @return array
	 */
	public static function setConversationAsRead(int $user_id, string $need_read_message_map, array $left_menu_row):array {

		$updated_at             = time();
		$is_version_update_need = false;

		// если есть непрочитанные
		if ($left_menu_row["unread_count"] > 0) {

			Gateway_Db_CompanyConversation_UserInbox::set($user_id, [
				"conversation_unread_count" => "conversation_unread_count - 1",
				"message_unread_count"      => "message_unread_count - " . $left_menu_row["unread_count"],
				"updated_at"                => $updated_at,
			]);

			$is_version_update_need = true;
		}

		$set = [
			"last_read_message_map" => $need_read_message_map,
			"unread_count"          => 0,
			"is_have_notice"        => 0,
			"version"               => $left_menu_row["version"],
		];

		$left_menu_row["last_read_message_map"] = $need_read_message_map;
		$left_menu_row["unread_count"]          = 0;
		$left_menu_row["is_have_notice"]        = 0;

		if ($left_menu_row["is_mentioned"] == 1) {

			$set["is_mentioned"]  = 0;
			$set["mention_count"] = 0;
			$set["updated_at"]    = $updated_at;

			$left_menu_row["is_mentioned"]  = 0;
			$left_menu_row["mention_count"] = 0;
			$left_menu_row["updated_at"]    = $updated_at;
		}

		$left_menu_row["version"] = Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $left_menu_row["conversation_map"], $set, $is_version_update_need);
		return $left_menu_row;
	}
}
