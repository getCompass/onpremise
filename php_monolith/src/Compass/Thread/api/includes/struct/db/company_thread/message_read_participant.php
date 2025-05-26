<?php

namespace Compass\Thread;

/**
 * класс-структура для таблицы company_thread.message_read_participants
 */
class Struct_Db_CompanyThread_MessageReadParticipant {

	/**
	 * @param string $thread_map
	 * @param int    $thread_message_index
	 * @param int    $user_id
	 * @param int    $read_at
	 * @param int    $message_created_at
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param string $message_map
	 */
	public function __construct(
		public string $thread_map,
		public int    $thread_message_index,
		public int    $user_id,
		public int    $read_at,
		public int    $message_created_at,
		public int    $created_at,
		public int    $updated_at,
		public string $message_map
	) {

	}
}