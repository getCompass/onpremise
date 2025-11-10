<?php

namespace Compass\Federation;

/**
 * класс описывает структуру сущности ldap_data . mail_confirm_via_code_story
 * @package Compass\Federation
 */
class Struct_Db_LdapData_MailConfirmViaCodeStory {

	public function __construct(
		public ?int   $id,
		public int    $status,
		public int    $resend_count,
		public int    $error_count,
		public int    $created_at,
		public int    $updated_at,
		public int    $next_resend_at,
		public int    $mail_confirm_story_id,
		public string $message_id,
		public string $code_hash,
		public string $mail,
	) {
	}

	/**
	 * конвертируем запись в структуру
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_LdapData_MailConfirmViaCodeStory
	 */
	public static function rowToStruct(array $row):Struct_Db_LdapData_MailConfirmViaCodeStory {

		return new Struct_Db_LdapData_MailConfirmViaCodeStory(
			(int) $row["id"],
			(int) $row["status"],
			(int) $row["resend_count"],
			(int) $row["error_count"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
			(int) $row["next_resend_at"],
			(int) $row["mail_confirm_story_id"],
			(string) $row["message_id"],
			(string) $row["code_hash"],
			(string) $row["mail"],
		);
	}
}