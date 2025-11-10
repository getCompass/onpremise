<?php

namespace Compass\Conversation;

/**
 * Пометить чат непрочитанным
 */
class Domain_Conversation_Action_SetAsUnread {

	/**
	 * Пометить чат непрочитанным
	 *
	 * @param int    $user_id
	 * @param string $conversation_map
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws cs_LeftMenuRowIsNotExist
	 */
	public static function do(int $user_id, string $conversation_map):void {

		Gateway_Db_CompanyConversation_Main::beginTransaction();
		$left_menu_row = Gateway_Db_CompanyConversation_UserLeftMenu::getForUpdate($user_id, $conversation_map);

		// запись в левом меню не существует
		if (!isset($left_menu_row["user_id"])) {

			Gateway_Db_CompanyConversation_Main::rollback();
			throw new cs_LeftMenuRowIsNotExist();
		}

		if (Type_Conversation_Meta::isHiringConversation($left_menu_row["type"])) {

			self::_liftUpConversation($user_id, $left_menu_row);
			Gateway_Db_CompanyConversation_Main::commitTransaction();
			return;
		}

		// если в чате уже есть непрочитанные сообщения
		if ($left_menu_row["unread_count"] != 0) {

			self::_liftUpConversation($user_id, $left_menu_row);
			Gateway_Db_CompanyConversation_Main::commitTransaction();
			return;
		}

		// если чат был прочитанный - поднимаем число непрочитанных
		self::_incrementUnreadCount($user_id, $left_menu_row);
		Gateway_Db_CompanyConversation_Main::commitTransaction();
	}

	/**
	 * Инкрементим непрочитанные
	 */
	protected static function _incrementUnreadCount(int $user_id, array $left_menu_row):void {

		$set_user_inbox = [
			"message_unread_count"      => "message_unread_count + 1",
			"conversation_unread_count" => "conversation_unread_count + 1",
			"updated_at"                => time(),
		];

		// если сингл диалог
		if (Type_Conversation_Meta::isSubtypeOfSingle($left_menu_row["type"])) {
			$set_user_inbox["single_conversation_unread_count"] = "single_conversation_unread_count + 1";
		}

		Gateway_Db_CompanyConversation_UserInbox::set($user_id, $set_user_inbox);

		$set = [
			"unread_count"   => "unread_count + 1",
			"updated_at"     => time(),
			"is_have_notice" => 1,
			"version"        => $left_menu_row["version"],
		];

		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $left_menu_row["conversation_map"], $set);
	}

	/**
	 * Выполняем если чат уже был непрочитанный
	 */
	protected static function _liftUpConversation(int $user_id, array $left_menu_row):void {

		Gateway_Db_CompanyConversation_UserInbox::set($user_id, [
			"updated_at" => time(),
		]);

		$set = [
			"updated_at" => time(),
			"version"    => $left_menu_row["version"],
		];

		Domain_User_Action_Conversation_UpdateLeftMenu::do($user_id, $left_menu_row["conversation_map"], $set);
	}
}