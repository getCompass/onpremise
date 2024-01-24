<?php

namespace Compass\Thread;

/**
 * класс-структура для таблицы company_data . remind_list
 */
class Struct_Db_CompanyData_Remind {

	/**
	 * Struct_Db_CompanyData_Remind constructor.
	 *
	 * @param int    $remind_id
	 * @param int    $is_done
	 * @param int    $type
	 * @param int    $remind_at
	 * @param int    $creator_user_id
	 * @param int    $created_at
	 * @param int    $updated_at
	 * @param string $recipient_id
	 * @param array  $data
	 */
	public function __construct(
		public int $remind_id,
		public int $is_done,
		public int $type,
		public int $remind_at,
		public int $creator_user_id,
		public int $created_at,
		public int $updated_at,
		public string $recipient_id,
		public array $data
	) {

	}
}