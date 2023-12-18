<?php

namespace Compass\Company;

/**
 * Класс-структура для таблицы company_data.member_menu
 */
class Struct_Db_CompanyData_MemberMenu {

	/**
	 * Struct_Db_CompanyData_MemberMenu constructor.
	 *
	 * @param int|null $notification_id
	 * @param int $user_id
	 * @param int $action_user_id
	 * @param int $type
	 * @param int $is_unread
	 * @param int $created_at
	 * @param int $updated_at
	 */
	public function __construct(
		public int|null $notification_id,
		public int $user_id,
		public int $action_user_id,
		public int $type,
		public int $is_unread,
		public int $created_at,
		public int $updated_at,
	) {

	}
}