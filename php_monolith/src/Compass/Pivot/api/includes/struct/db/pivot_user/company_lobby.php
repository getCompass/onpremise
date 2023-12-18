<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_user_{10m}.company_lobby_list_{1}
 */
class Struct_Db_PivotUser_CompanyLobby {

	/**
	 * @param int   $user_id
	 * @param int   $company_id
	 * @param int   $order
	 * @param int   $status
	 * @param int   $entry_id
	 * @param int   $created_at
	 * @param int   $updated_at
	 * @param array $extra
	 */
	public function __construct(
		public int   $user_id,
		public int   $company_id,
		public int   $order,
		public int   $status,
		public int   $entry_id,
		public int   $created_at,
		public int   $updated_at,
		public array $extra,
	) {

	}
}