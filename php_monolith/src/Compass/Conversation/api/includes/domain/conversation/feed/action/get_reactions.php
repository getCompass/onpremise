<?php

namespace Compass\Conversation;

/**
 * Получаем список реакций
 */
class Domain_Conversation_Feed_Action_GetReactions {

	/**
	 * @param string $conversation_map
	 * @param array  $block_id_list
	 *
	 * @return array
	 * @throws \parseException
	 */
	public static function run(string $conversation_map, array $block_id_list):array {

		$message_block_reaction_list = Gateway_Db_CompanyConversation_MessageBlockReactionList::getList($conversation_map, $block_id_list);

		$output = [];
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
				$reaction_item_list[] = [
					"message_map"        => $message_map,
					"reaction_user_list" => $temp,
				];
			}
			$output[] = [
				"block_id"      => $message_block_reaction_row->block_id,
				"reaction_list" => $reaction_item_list,
			];
		}
		return $output;
	}
}
