<?php

namespace Compass\Federation;

/**
 * класс описывает структуру сущности ldap_data . mail_confirm_story
 * @package Compass\Federation
 */
class Struct_Db_LdapData_MailConfirmStory {

	public function __construct(
		public ?int   $mail_confirm_story_id,
		public int    $status,
		public int    $stage,
		public int    $created_at,
		public int    $updated_at,
		public int    $expires_at,
		public string $ldap_auth_token,
		public string $uid,
	) {
	}

	/**
	 * конвертируем запись в структуру
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_LdapData_MailConfirmStory
	 */
	public static function rowToStruct(array $row):Struct_Db_LdapData_MailConfirmStory {

		return new Struct_Db_LdapData_MailConfirmStory(
			(int) $row["mail_confirm_story_id"],
			(int) $row["status"],
			(int) $row["stage"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
			(int) $row["expires_at"],
			(string) $row["ldap_auth_token"],
			(string) $row["uid"],
		);
	}
}