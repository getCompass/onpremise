<?php

namespace Compass\Thread;

/**
 * класс-структура для прочитавшего участника для таблицы company_conversation.read_message_participants
 */
class Struct_Db_CompanyThread_MessageReadParticipant_Participant {

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