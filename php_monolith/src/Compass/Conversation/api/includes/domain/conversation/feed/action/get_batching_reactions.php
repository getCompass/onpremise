<?php

namespace Compass\Conversation;

/**
 * Получаем батчингом список реакций
 */
class Domain_Conversation_Feed_Action_GetBatchingReactions {

	/**
	 * выполняем
	 *
	 * @param array $block_id_list_by_conversation_map
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function run(array $block_id_list_by_conversation_map):array {

		/** @var Struct_Db_CompanyConversation_MessageBlockReaction[] $message_block_reaction_list */
		$message_block_reaction_list = Gateway_Db_CompanyConversation_MessageBlockReactionList::getSpecifiedList($block_id_list_by_conversation_map);

		$reaction_list_by_conversation_map = [];
		foreach ($message_block_reaction_list as $message_block_reaction_row) {

			$reaction_list = $message_block_reaction_row->getReactionList();

			$reaction_item_list = [];
			foreach ($reaction_list as $message_map => $reaction_user_list) {

				// так как json изменяется в go
				// проверка на null потому что так go удаляет записи :(
				if (is_null($reaction_user_list)) {
					continue;
				}

				$temp                 = Domain_Conversation_Action_Message_GetFromReactionList::do($reaction_user_list);
				$reaction_item_list[$message_map] = [
					"message_map"        => $message_map,
					"reaction_user_list" => $temp,
				];
			}

			$reaction_list_by_conversation_map[$message_block_reaction_row->conversation_map][$message_block_reaction_row->block_id] = [
				"block_id"      => $message_block_reaction_row->block_id,
				"reaction_list" => $reaction_item_list,
			];
		}

		foreach ($block_id_list_by_conversation_map as $conversation_map => $_) {
			$reaction_list_by_conversation_map[$conversation_map] = $reaction_list_by_conversation_map[$conversation_map] ?? [];
		}

		return $reaction_list_by_conversation_map;
	}
}
