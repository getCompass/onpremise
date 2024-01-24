<?php

namespace Compass\Conversation;

/**
 * Фильтруем список блоков оставляя только актуальные
 */
class Domain_Conversation_Feed_Action_FilterBlockIdList {

	/**
	 * @param Struct_Db_CompanyConversation_ConversationDynamic $dynamic_row
	 * @param array                                             $block_id_list
	 *
	 * @return array
	 */
	public static function run(Struct_Db_CompanyConversation_ConversationDynamic $dynamic_row, array $block_id_list):array {

		$final_block_id_list = [];

		// если блок который нужен пользователю горячий
		foreach ($block_id_list as $block_id) {

			if ($block_id < $dynamic_row->start_block_id || $block_id > $dynamic_row->last_block_id) {
				continue;
			}
			$final_block_id_list[] = $block_id;
		}
		return $final_block_id_list;
	}
}
