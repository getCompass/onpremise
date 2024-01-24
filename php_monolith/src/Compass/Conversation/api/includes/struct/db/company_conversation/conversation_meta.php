<?php

namespace Compass\Conversation;

/**
 * класс-структура для таблицы company_conversation.conversation_meta
 */
class Struct_Db_CompanyConversation_ConversationMeta {

	/**
	 * @param string conversation_map
	 * @param int allow_status
	 * @param int type
	 * @param int created_at
	 * @param int updated_at
	 * @param int creator_user_id
	 * @param string avatar_file_map
	 * @param string conversation_name
	 * @param array users
	 * @param array extra
	 */
	public function __construct(
		public string $conversation_map,
		public int    $allow_status,
		public int    $type,
		public int    $created_at,
		public int    $updated_at,
		public int    $creator_user_id,
		public string $avatar_file_map,
		public string $conversation_name,
		public array  $users,
		public array  $extra,
	) {

	}
}