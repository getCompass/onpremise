<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_mail.mail_uniq_list_{0-f}
 */
class Struct_Db_PivotMail_MailUniq {

	/**
	 * Struct_Db_PivotMail_MailUniq constructor.
	 */
	public function __construct(
		public string $mail_hash,
		public int    $user_id,
		public bool   $has_sso_account,
		public int    $created_at,
		public int    $updated_at,
		public string $password_hash,
	) {
	}

	/**
	 * конвертируем запись в структуру
	 *
	 * @return Struct_Db_PivotMail_MailUniq
	 */
	public static function rowToStruct(array $row):Struct_Db_PivotMail_MailUniq {

		return new Struct_Db_PivotMail_MailUniq(
			(string) $row["mail_hash"],
			(int) $row["user_id"],
			(bool) boolval($row["has_sso_account"]),
			(int) $row["created_at"],
			(int) $row["updated_at"],
			(string) $row["password_hash"],
		);
	}
}