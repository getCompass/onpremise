<?php declare(strict_types=1);

namespace Compass\Conversation;

/**
 * Структура, описывающая локацию поиска «Диалог».
 */
#[\JetBrains\PhpStorm\Immutable]
class Struct_Domain_Search_Location_Conversation {

	/**
	 * Конструктор.
	 */
	public function __construct(
		public array                                             $left_menu_item,
		public array                                             $conversation_meta,
		public Struct_Db_CompanyConversation_ConversationDynamic $conversation_dynamic,
		public int                                               $hit_count,
		public array                                             $hit_list = []
	) {

		// nothing
	}
}
