<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_user_{10m}.user_security_{1}
 */
class Struct_Db_PivotUser_UserSecurity {

	/**
	 * Struct_Db_PivotUserSecurity_UserSecurity constructor.
	 *
	 */
	public function __construct(
		public int    $user_id,
		public string $phone_number,
		public string $mail,
		public int    $created_at,
		public int    $updated_at
	) {
	}

	/**
	 * конвертируем запись в структуру
	 *
	 * @return Struct_Db_PivotUser_UserSecurity
	 */
	public static function rowToStruct(array $row):Struct_Db_PivotUser_UserSecurity {

		return new Struct_Db_PivotUser_UserSecurity(
			(int) $row["user_id"],
			(string) $row["phone_number"],
			(string) $row["mail"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
		);
	}
}