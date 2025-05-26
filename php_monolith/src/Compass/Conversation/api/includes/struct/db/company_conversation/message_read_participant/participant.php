<?php

namespace Compass\Conversation;

/**
 * класс-структура для прочитавшего участника для таблицы company_conversation.message_read_particioants
 */
class Struct_Db_CompanyConversation_MessageReadParticipant_Participant {

	/**
	 * @param int $user_id
	 * @param int $read_at
	 */
	public function __construct(
		public int $user_id,
		public int $read_at
	) {

	}
}