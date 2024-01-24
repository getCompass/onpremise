<?php

namespace Compass\Conversation;

/**
 * класс-структура для таблицы company_conversation.file
 */
class Struct_Db_CompanyConversation_ConversationFile {

	/**
	 * @param string   $file_uuid
	 * @param int|null $row_id
	 * @param string   $conversation_map
	 * @param string   $file_map
	 * @param int      $user_id
	 * @param int      $is_deleted
	 * @param int      $file_type
	 * @param int      $parent_type
	 * @param int      $conversation_message_created_at
	 * @param int      $created_at
	 * @param int      $updated_at
	 * @param string   $parent_message_map
	 * @param string   $conversation_message_map
	 * @param array    $extra
	 */
	public function __construct(
		public string   $file_uuid,
		public int|null $row_id,
		public string   $conversation_map,
		public string   $file_map,
		public int      $file_type,
		public int      $parent_type,
		public int      $conversation_message_created_at,
		public int      $is_deleted,
		public int      $user_id,
		public int      $created_at,
		public int      $updated_at,
		public string   $parent_message_map,
		public string   $conversation_message_map,
		public array    $extra,
	) {

	}
}