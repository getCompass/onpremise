<?php

namespace Compass\Conversation;

/**
 * Получаем последние блоки
 */
class Domain_Conversation_Feed_Action_GetLastBlockIdList {

	public const MAX_GET_MESSAGES_BLOCK_COUNT_FOR_EMPTY_BLOCK_ID_LIST = 3;  // максимальное количество блоков в getMessages при передачи пустого block_id_list
	public const MAX_GET_MESSAGES_BLOCK_COUNT                         = 5;  // максимальное количество блоков в getMessages

	/**
	 * @param Struct_Db_CompanyConversation_ConversationDynamic $dynamic_row
	 * @param int                                               $block_count
	 *
	 * @return array
	 */
	public static function run(Struct_Db_CompanyConversation_ConversationDynamic $dynamic_row, int $block_count):array {

		$start_block_id = $dynamic_row->last_block_id - $block_count;
		if ($start_block_id < $dynamic_row->start_block_id) {
			$start_block_id = $dynamic_row->start_block_id;
		}
		return range($start_block_id, $dynamic_row->last_block_id);
	}
}
