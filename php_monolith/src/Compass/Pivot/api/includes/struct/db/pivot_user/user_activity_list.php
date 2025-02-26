<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_user_{10m}.user_activity_list_{1}
 */
class Struct_Db_PivotUser_UserActivityList {

	/**
	 * Struct_Db_PivotUserSecurity_UserSecurity constructor.
	 *
	 */
	public function __construct(
		public int $user_id,
		public int $status,
		public int $created_at,
		public int $updated_at,
		public int $last_ws_ping_at
	) {
	}

	/**
	 * конвертируем запись в структуру
	 *
	 * @return Struct_Db_PivotUser_UserActivityList
	 */
	public static function rowToStruct(array $row):Struct_Db_PivotUser_UserActivityList {

		return new Struct_Db_PivotUser_UserActivityList(
			(int) $row["user_id"],
			(int) $row["status"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
			(int) $row["last_ws_ping_at"],
		);
	}
}