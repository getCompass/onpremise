<?php

namespace Compass\Conversation;

/**
 * Действие для получения данных сообщения для отправки Напоминания
 */
class Domain_Conversation_Action_Message_GetMessageDataForSendRemind {

	/**
	 * выполняем
	 *
	 * @throws \parseException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \cs_UnpackHasFailed
	 * @throws \paramException
	 */
	public static function do(string $message_map, int $user_id):array {

		$conversation_map = \CompassApp\Pack\Message\Conversation::getConversationMap($message_map);
		$meta_row         = Type_Conversation_Meta::get($conversation_map);

		// получаем блок на обновление, достаём сообщение
		$block_id  = \CompassApp\Pack\Message\Conversation::getBlockId($message_map);
		$block_row = Gateway_Db_CompanyConversation_MessageBlock::getOne($conversation_map, $block_id);
		$message   = Domain_Conversation_Entity_Message_Block_Message::get($message_map, $block_row);

		$remind_id = Type_Conversation_Message_Main::getHandler($message)::getRemindId($message);

		// получаем thread_map
		$thread_rel = Type_Conversation_ThreadRel::getThreadRelByMessageMap($conversation_map, $message_map);

		// удаляем данные Напоминания
		$message = Type_Conversation_Message_Main::getHandler($message)::removeRemindData($message);

		// обновляем временную метку и версию обновления сообщений в диалоге
		$dynamic = self::_updateConversationDynamic($conversation_map);

		$talking_user_list = Type_Conversation_Meta_Users::getTalkingUserList($meta_row["users"]);
		Gateway_Bus_Sender::remindDeleted($remind_id, $message_map, $conversation_map, $dynamic->messages_updated_version, $talking_user_list);

		// подготавливаем массив $message_data к ответу
		return self::_prepareOutput($message, $thread_rel, $user_id);
	}

	/**
	 * обновляем временную метку и версию обновления сообщений в диалоге
	 *
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 * @throws \cs_RowIsEmpty
	 */
	protected static function _updateConversationDynamic(string $conversation_map):Struct_Db_CompanyConversation_ConversationDynamic {

		Gateway_Db_CompanyConversation_ConversationDynamic::beginTransaction();
		$dynamic = Gateway_Db_CompanyConversation_ConversationDynamic::getForUpdate($conversation_map);

		$dynamic->messages_updated_at      = time();
		$dynamic->messages_updated_version = $dynamic->messages_updated_version + 1;
		$dynamic->updated_at               = time();

		$set = [
			"messages_updated_at"      => $dynamic->messages_updated_at,
			"messages_updated_version" => $dynamic->messages_updated_version,
			"updated_at"               => $dynamic->updated_at,
		];
		Gateway_Db_CompanyConversation_ConversationDynamic::set($conversation_map, $set);
		Gateway_Db_CompanyConversation_ConversationDynamic::commitTransaction();

		return $dynamic;
	}

	// подготавливаем output для ответа в getMessage
	protected static function _prepareOutput(array $message, array $thread_rel, int $user_id):array {

		// прикрепляем тред, если он есть
		if (isset($thread_rel[$message["message_map"]])) {

			$message["child_thread"]["thread_map"] = $thread_rel[$message["message_map"]]["thread_map"];

			$is_hidden = 0;
			if (in_array($user_id, $thread_rel[$message["message_map"]]["thread_hidden_user_list"])) {
				$is_hidden = 1;
			}
			if ($thread_rel[$message["message_map"]]["is_thread_hidden_for_all_users"] === 1) {
				$is_hidden = 1;
			}

			$message["child_thread"]["is_hidden"] = (int) $is_hidden;
		}

		return [
			"message"              => $message,
			"thread_rel"           => $thread_rel,
			"reaction_count_list"  => [],
			"last_reaction_edited" => 0,
		];
	}
}