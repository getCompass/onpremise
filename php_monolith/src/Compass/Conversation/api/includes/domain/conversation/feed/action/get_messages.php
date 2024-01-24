<?php

namespace Compass\Conversation;

/**
 * Получаем список сообщений
 */
class Domain_Conversation_Feed_Action_GetMessages {

	/**
	 * @param int                                               $user_id
	 * @param Struct_Db_CompanyConversation_ConversationMeta    $meta_row
	 * @param Struct_Db_CompanyConversation_ConversationDynamic $dynamic_row
	 * @param array                                             $block_id_list
	 *
	 * @return array
	 * @throws \parseException
	 * @long
	 */
	public static function run(int $user_id, Struct_Db_CompanyConversation_ConversationMeta $meta_row, Struct_Db_CompanyConversation_ConversationDynamic $dynamic_row, array $block_id_list):array {

		$block_list  = Domain_Conversation_Entity_Message_Block_Get::getBlockListRowByIdList($dynamic_row->conversation_map, $block_id_list);
		$clear_until = Domain_Conversation_Entity_Dynamic::getClearUntil($dynamic_row->user_clear_info, $dynamic_row->conversation_clear_info, $user_id);

		$message_list = [];
		$users        = [];

		// проходим каждому сообщению из горячего блока
		foreach ($block_list as $block_row) {

			foreach ($block_row["data"] as $message) {

				// если пользователь не может просматривать сообщение (например скрыл его)
				if (Type_Conversation_Message_Main::getHandler($message)::isMessageHiddenForUser($message, $user_id)) {
					continue;
				}

				// если дата создания сообщения раньше, чем отметка до которой пользователь очистил диалог
				if (Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message) < $clear_until) {
					continue;
				}

				// если отключен показ системных сообщений о приглашении/вступлении в группу
				if (Type_Conversation_Message_Main::getHandler($message)::isNeedHideSystemMessageOnInviteAndJoin($message, $meta_row->extra)) {
					continue;
				}

				// если отключен показ системных сообщений о покидании/кике из группы
				if (Type_Conversation_Message_Main::getHandler($message)::isNeedHideSystemMessageOnLeaveAndKicked($message, $meta_row->extra)) {
					continue;
				}

				// если отключен показ удалённых сообщений в группе
				if (Type_Conversation_Message_Main::getHandler($message)::isNeedHideSystemDeletedMessage($message, $meta_row->extra)) {
					continue;
				}

				$message_list[] = Type_Conversation_Message_Main::getHandler($message)::prepareForFormat($message);

				// добавляем пользователей к ответу
				$users = array_merge($users, Type_Conversation_Message_Main::getHandler($message)::getUsers($message));
			}
		}

		$min_block_created_at = 0;

		if (count($block_list) != 0) {

			// получаем created_at минимального блока
			$min_block_created_at = (int) min(array_column($block_list, "created_at"));
		}

		return [$message_list, $users, $min_block_created_at];
	}
}
