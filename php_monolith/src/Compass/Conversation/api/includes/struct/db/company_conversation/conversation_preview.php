<?php

namespace Compass\Conversation;

/**
 * класс-структура для таблицы company_conversation.conversation_preview
 */
class Struct_Db_CompanyConversation_ConversationPreview {

	/**
	 * @param int    $parent_type
	 * @param string $parent_message_map
	 * @param int    $is_deleted
	 * @param int    $conversation_message_created_at
	 * @param int    $parent_message_created_at
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param int    $user_id
	 * @param string $preview_map
	 * @param string $conversation_map
	 * @param string $conversation_message_map
	 * @param array  $link_list
	 * @param array  $hidden_by_user_list
	 */
	public function __construct(
		public int    $parent_type,
		public string $parent_message_map,
		public int    $is_deleted,
		public int    $conversation_message_created_at,
		public int    $parent_message_created_at,
		public int    $created_at,
		public int    $updated_at,
		public int    $user_id,
		public string $preview_map,
		public string $conversation_map,
		public string $conversation_message_map,
		public array  $link_list,
		public array  $hidden_by_user_list,
	) {

	}
}