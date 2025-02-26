<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_session_logs_{Y}.session_history
 */
class Struct_Db_PivotHistoryLogs_SessionHistory {

	/**
	 * Struct_Db_PivotHistoryLogs_SessionHistory constructor.
	 *
	 */
	public function __construct(
		public string $session_uniq,
		public int    $user_id,
		public int    $status,
		public int    $login_at,
		public int    $logout_at,
		public string $ua_hash,
		public string $ip_address,
		public array  $extra
	) {
	}
}