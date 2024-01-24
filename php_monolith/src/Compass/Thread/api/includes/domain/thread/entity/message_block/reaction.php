<?php

namespace Compass\Thread;

/**
 * Класс для работы с сообщениями в блоках
 */
class Domain_Thread_Entity_MessageBlock_Reaction {

	/**
	 * Достает из блока реакция список реакций к сообщению.
	 */
	public static function fetchMessageReactionData(Struct_Db_CompanyThread_MessageBlockReaction $reaction_count_block_row, string $message_map):array {

		$reaction_list           = [];
		$reaction_last_edited_at = 0;

		$message_reaction_list = $reaction_count_block_row->getMessageReactionList($message_map);

		if (count($message_reaction_list) < 1) {
			return [[], 0];
		}

		foreach ($message_reaction_list as $reaction_name => $user_list) {

			asort($user_list);
			$reaction_list[$reaction_name] = array_keys($user_list);

			foreach ($user_list as $updated_at_ms) {
				$reaction_last_edited_at = max($reaction_last_edited_at, $updated_at_ms);
			}
		}

		// подготавливаем сообщение к форматированию
		return [$reaction_list, $reaction_last_edited_at];
	}
}