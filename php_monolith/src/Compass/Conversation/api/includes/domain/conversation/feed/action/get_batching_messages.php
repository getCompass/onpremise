<?php

namespace Compass\Conversation;

/**
 * Получаем список сообщений батчингом
 */
class Domain_Conversation_Feed_Action_GetBatchingMessages {

	/**
	 * выполняем
	 *
	 * @param int                                                 $user_id
	 * @param Struct_Db_CompanyConversation_ConversationMeta[]    $meta_list
	 * @param Struct_Db_CompanyConversation_ConversationDynamic[] $dynamic_list
	 * @param array                                               $block_id_list_by_conversation_map
	 *
	 * @return array
	 * @long
	 * @throws \parseException
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function run(int $user_id, array $meta_list, array $dynamic_list, array $block_id_list_by_conversation_map):array {

		$clear_until_by_conversation_map = [];
		foreach ($block_id_list_by_conversation_map as $conversation_map => $block_id_list) {

			$block_id_list_by_conversation_map[$conversation_map] = array_values(array_unique($block_id_list));

			$dynamic     = $dynamic_list[$conversation_map];
			$clear_until = Domain_Conversation_Entity_Dynamic::getClearUntil($dynamic->user_clear_info, $dynamic->conversation_clear_info, $user_id);

			$clear_until_by_conversation_map[$conversation_map] = $clear_until;
		}

		// получаем связь сообщений и тредов
		$thread_rel_list                     = Gateway_Db_CompanyConversation_MessageThreadRel::getSpecifiedList($block_id_list_by_conversation_map);
		$thread_rel_list_by_conversation_map = [];
		foreach ($thread_rel_list as $thread_rel) {

			$thread_rel_list_by_conversation_map[$thread_rel->conversation_map] = Type_Conversation_ThreadRel::prepareThreadRelData(
				$thread_rel_list_by_conversation_map[$thread_rel->conversation_map] ?? [], $thread_rel
			);
		}

		$message_block_list = Gateway_Db_CompanyConversation_MessageBlock::getSpecifiedList($block_id_list_by_conversation_map);

		// получаем батчингом реакции для выбранных диалогов
		$reaction_list_by_conversation_map = Domain_Conversation_Feed_Action_GetBatchingReactions::run($block_id_list_by_conversation_map);

		$users = [];

		// проходим каждому сообщению из горячего блока
		$hidden_message_map_list_by_conversation_map = [];
		$message_list_by_conversation_map            = [];
		$min_block_created_at_by_conversation_map    = [];
		foreach ($message_block_list as $block_row) {

			$conversation_map = $block_row["conversation_map"];
			$block_id         = (int) $block_row["block_id"];

			// получаем created_at минимального блока для диалога
			$block_created_at     = (int) $block_row["created_at"];
			$min_block_created_at = $min_block_created_at_by_conversation_map[$conversation_map] ?? $block_created_at;

			$min_block_created_at_by_conversation_map[$conversation_map] = min($block_created_at, $min_block_created_at);

			// получаем реакции для диалога, затем для блока
			$reaction_list_by_block_id_list = $reaction_list_by_conversation_map[$conversation_map] ?? [];
			$prepare_thread_rel_list        = $thread_rel_list_by_conversation_map[$conversation_map] ?? [];
			$messages_reaction_list         = $reaction_list_by_block_id_list[$block_id] ?? [];

			// получаем сообщения, доступные для пользователя
			$meta        = $meta_list[$conversation_map];
			$clear_until = $clear_until_by_conversation_map[$conversation_map];
			foreach ($block_row["data"] as $message) {

				$message_map = $message["message_map"];

				// если пользователь не может просматривать сообщение (например скрыл его)
				if (Type_Conversation_Message_Main::getHandler($message)::isMessageHiddenForUser($message, $user_id)) {

					$hidden_message_map_list_by_conversation_map[$conversation_map][] = $message_map;
					continue;
				}

				// если дата создания сообщения раньше, чем отметка до которой пользователь очистил диалог
				if (Type_Conversation_Message_Main::getHandler($message)::getCreatedAt($message) < $clear_until) {

					$hidden_message_map_list_by_conversation_map[$conversation_map][] = $message_map;
					continue;
				}

				// если отключен показ системных сообщений о приглашении/вступлении в группу
				if (Type_Conversation_Message_Main::getHandler($message)::isNeedHideSystemMessageOnInviteAndJoin($message, $meta->extra)) {

					$hidden_message_map_list_by_conversation_map[$conversation_map][] = $message_map;
					continue;
				}

				// если отключен показ системных сообщений о покидании/кике из группы
				if (Type_Conversation_Message_Main::getHandler($message)::isNeedHideSystemMessageOnLeaveAndKicked($message, $meta->extra)) {

					$hidden_message_map_list_by_conversation_map[$conversation_map][] = $message_map;
					continue;
				}

				// если отключен показ удалённых сообщений в группе
				if (Type_Conversation_Message_Main::getHandler($message)::isNeedHideSystemDeletedMessage($message, $meta->extra)) {

					$hidden_message_map_list_by_conversation_map[$conversation_map][] = $message_map;
					continue;
				}

				// достаём реакции для сообщения
				$reaction_list      = $messages_reaction_list["reaction_list"][$message_map] ?? [];
				$reaction_user_list = $reaction_list["reaction_user_list"] ?? [];

				$message_list_by_conversation_map[$conversation_map][] = array_merge(
					$message_list_by_conversation_map[$conversation_map] ?? [],
					Type_Conversation_Message_Main::getHandler($message)::prepareForFormat($message, $user_id, $reaction_user_list, $prepare_thread_rel_list, true)
				);

				// добавляем пользователей к ответу
				$users = array_merge($users, Type_Conversation_Message_Main::getHandler($message)::getUsers($message));
			}
		}

		foreach ($block_id_list_by_conversation_map as $conversation_map => $block_id_list) {

			if (!isset($message_list_by_conversation_map[$conversation_map])) {

				$message_list_by_conversation_map[$conversation_map]            = [];
				$min_block_created_at_by_conversation_map[$conversation_map]    = $min_block_created_at_by_conversation_map[$conversation_map] ?? 0;
				$hidden_message_map_list_by_conversation_map[$conversation_map] = $hidden_message_map_list_by_conversation_map[$conversation_map] ?? [];
			}
		}

		return [$message_list_by_conversation_map, $users, $min_block_created_at_by_conversation_map, $hidden_message_map_list_by_conversation_map];
	}
}
