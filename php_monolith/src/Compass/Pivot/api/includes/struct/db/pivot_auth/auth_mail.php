<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_auth_{Y}.auth_mail_list_{M}
 */
class Struct_Db_PivotAuth_AuthMail extends Struct_Db_PivotAuth_AuthDefault {

	/**
	 * Struct_Db_PivotAuth_AuthMail constructor.
	 */
	public function __construct(
		public string $auth_map,
		public int    $is_success,
		public int    $has_password,
		public int    $has_code,
		public int    $resend_count,
		public int    $password_error_count,
		public int    $code_error_count,
		public int    $created_at,
		public int    $updated_at,
		public int    $next_resend_at,
		public string $message_id,
		public string $code_hash,
		public string $mail,
	) {
	}

	/**
	 * конвертируем запись в структуру
	 *
	 * @return Struct_Db_PivotAuth_AuthMail
	 */
	public static function rowToStruct(array $row):Struct_Db_PivotAuth_AuthMail {

		return new Struct_Db_PivotAuth_AuthMail(
			(string) $row["auth_map"],
			(int) $row["is_success"],
			(int) $row["has_password"],
			(int) $row["has_code"],
			(int) $row["resend_count"],
			(int) $row["password_error_count"],
			(int) $row["code_error_count"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
			(int) $row["next_resend_at"],
			(string) $row["message_id"],
			(string) $row["code_hash"],
			(string) $row["mail"],
		);
	}
}