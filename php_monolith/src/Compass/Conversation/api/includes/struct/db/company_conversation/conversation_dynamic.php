<?php

namespace Compass\Conversation;

/**
 * класс-структура для таблицы company_conversation.conversation_dynamic
 */
class Struct_Db_CompanyConversation_ConversationDynamic {

	/**
	 * @param string $conversation_map
	 * @param int    $is_locked
	 * @param int    $last_block_id
	 * @param int    $start_block_id
	 * @param int    $total_message_count
	 * @param int    $total_action_count
	 * @param int    $file_count
	 * @param int    $image_count
	 * @param int    $video_count
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param int    $messages_updated_at
	 * @param int    $reactions_updated_at
	 * @param int    $threads_updated_at
	 * @param int    $messages_updated_version
	 * @param int    $reactions_updated_version
	 * @param int    $threads_updated_version
	 * @param array  $user_mute_info
	 * @param array  $user_clear_info
	 * @param array  $user_file_clear_info
	 * @param array  $conversation_clear_info
	 */
	public function __construct(
		public string $conversation_map,
		public int    $is_locked,
		public int    $last_block_id,
		public int    $start_block_id,
		public int    $total_message_count,
		public int    $total_action_count,
		public int    $file_count,
		public int    $image_count,
		public int    $video_count,
		public int    $created_at,
		public int    $updated_at,
		public int    $messages_updated_at,
		public int    $reactions_updated_at,
		public int    $threads_updated_at,
		public int    $messages_updated_version,
		public int    $reactions_updated_version,
		public int    $threads_updated_version,
		public array  $user_mute_info,
		public array  $user_clear_info,
		public array  $user_file_clear_info,
		public array  $conversation_clear_info
	) {

	}
}