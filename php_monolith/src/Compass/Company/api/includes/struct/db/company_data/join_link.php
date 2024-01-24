<?php

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_data.join_link_list
 */
class Struct_Db_CompanyData_JoinLink {

	/**
	 * Struct_Db_CompanyData_JoinLink constructor.
	 *
	 * @param string $join_link_uniq
	 * @param int    $entry_option
	 * @param int    $status
	 * @param int    $type
	 * @param int    $can_use_count
	 * @param int    $expires_at
	 * @param int    $creator_user_id
	 * @param int    $created_at
	 * @param int    $updated_at
	 */
	public function __construct(
		public string $join_link_uniq,
		public int    $entry_option,
		public int    $status,
		public int    $type,
		public int    $can_use_count,
		public int    $expires_at,
		public int    $creator_user_id,
		public int    $created_at,
		public int    $updated_at,
	) {

	}
}
