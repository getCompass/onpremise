<?php

namespace Compass\Conversation;

/**
 * Получить следующий id блока с сообщениями
 */
class Domain_Conversation_Action_GetNextBlockId {

	/**
	 * Получить следующий id блока с сообщениями
	 * @param array $dynamic_row
	 * @param array $block_id_list
	 *
	 * @return int
	 */
	public static function do(array $dynamic_row, array $block_id_list):int {

		// следующий блок вычисляем
		$next_block_id = max($block_id_list) + 1;

		if ($next_block_id < $dynamic_row["start_block_id"]) {
			$next_block_id = $dynamic_row["start_block_id"];
		}

		if ($next_block_id > $dynamic_row["last_block_id"]) {
			$next_block_id = 0;
		}

		return $next_block_id;
	}
}