<?php

namespace Compass\Conversation;

/**
 * класс-структура для таблицы company_conversation.message_user_hidded_rel
 */
class Struct_Db_CompanyConversation_MessageUserHiddedRel {

	/**
	 * @param int    $user_id
	 * @param string $message_map
	 * @param int    $created_at
	 */
	public function __construct(
		public int    $user_id,
		public string $message_map,
		public int    $created_at,
	) {

	}
}