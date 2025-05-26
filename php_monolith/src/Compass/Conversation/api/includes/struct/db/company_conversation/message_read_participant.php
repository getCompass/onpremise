<?php

namespace Compass\Conversation;

/**
 * класс-структура для таблицы company_conversation.message_read_participants
 */
class Struct_Db_CompanyConversation_MessageReadParticipant {

	/**
	 * @param string $conversation_map
	 * @param int    $conversation_message_index
	 * @param int    $user_id
	 * @param int    $read_at
	 * @param int    $message_created_at
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param string $message_map
	 */
	public function __construct(
		public string $conversation_map,
		public int    $conversation_message_index,
		public int    $user_id,
		public int    $read_at,
		public int    $message_created_at,
		public int    $created_at,
		public int    $updated_at,
		public string $message_map
	) {

	}
}