<?php

namespace Compass\Conversation;

/**
 * класс-структура для таблицы company_conversation.message_thread_tel
 */
class Struct_Db_CompanyConversation_MessageThreadRel {

	/**
	 *
	 * @param string $conversation_map
	 * @param string $message_map
	 * @param string $thread_map
	 * @param int    $block_id
	 * @param array  $extra
	 */
	public function __construct(
		public string $conversation_map,
		public string $message_map,
		public string $thread_map,
		public int    $block_id,
		public array  $extra
	) {

	}
}