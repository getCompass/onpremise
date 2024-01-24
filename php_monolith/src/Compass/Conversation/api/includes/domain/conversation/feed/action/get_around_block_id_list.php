<?php

namespace Compass\Conversation;

/**
 * Получаем блоки вокруг наших
 */
class Domain_Conversation_Feed_Action_GetAroundBlockIdList {

	/**
	 * Внимание! функция не учитывает что block_id_list может быть 5,10,234. Она получит значения вокруг
	 *
	 * @param Struct_Db_CompanyConversation_ConversationDynamic $dynamic_row
	 * @param array                                             $block_id_list
	 * @param int                                               $block_count
	 *
	 * @return array
	 */
	public static function run(int $user_id, Struct_Db_CompanyConversation_ConversationDynamic $dynamic_row, array $block_id_list, int $min_block_created_at, int $block_count):array {

		$next_block_id_list     = self::_getNextBlockIdList($dynamic_row, $block_id_list, $block_count);
		$previous_block_id_list = self::_getPreviousBlockIdList($user_id, $dynamic_row, $block_id_list, $min_block_created_at, $block_count);

		return [$next_block_id_list, $previous_block_id_list];
	}

	##########################################################
	# region PROTECTED
	##########################################################

	/**
	 * Находим следующие блоки
	 *
	 * @param Struct_Db_CompanyConversation_ConversationDynamic $dynamic_row
	 * @param array                                             $block_id_list
	 * @param int                                               $block_count
	 *
	 * @return array
	 */
	protected static function _getNextBlockIdList(Struct_Db_CompanyConversation_ConversationDynamic $dynamic_row, array $block_id_list, int $block_count):array {

		$next_block_id_list = [];

		$next_block_id     = max($block_id_list) + 1;
		$end_next_block_id = max($block_id_list) + $block_count;

		if ($end_next_block_id > $dynamic_row->last_block_id) {
			$end_next_block_id = $dynamic_row->last_block_id;
		}

		if ($next_block_id <= $dynamic_row->last_block_id) {
			$next_block_id_list = range($next_block_id, $end_next_block_id);
		}

		return $next_block_id_list;
	}

	/**
	 * Получаем предыдущие блоки
	 *
	 * @param Struct_Db_CompanyConversation_ConversationDynamic $dynamic_row
	 * @param array                                             $block_id_list
	 * @param int                                               $block_count
	 *
	 * @return array
	 */
	protected static function _getPreviousBlockIdList(int $user_id, Struct_Db_CompanyConversation_ConversationDynamic $dynamic_row, array $block_id_list, int $min_block_created_at, int $block_count):array {

		$previous_block_id_list = [];

		$previous_block_id = min($block_id_list) - 1;

		if ($previous_block_id < 1) {
			return $previous_block_id_list;
		}

		$clear_until = Domain_Conversation_Entity_Dynamic::getClearUntil($dynamic_row->user_clear_info, $dynamic_row->conversation_clear_info, $user_id);

		// диалог очищен после создания текущего блока
		if ($clear_until >= $min_block_created_at) {
			return $previous_block_id_list;
		}

		$start_previous_block_id = min($block_id_list) - $block_count;
		if ($start_previous_block_id < $dynamic_row->start_block_id) {
			$start_previous_block_id = $dynamic_row->start_block_id;
		}

		if ($previous_block_id >= $dynamic_row->start_block_id) {
			$previous_block_id_list = range($start_previous_block_id, $previous_block_id);
		}

		$previous_block_id_list[0] != 0 ?: array_shift($previous_block_id_list);

		return $previous_block_id_list;
	}
}
