<?php

namespace Compass\Federation;

/**
 * класс описывает структуру сущности ldap_data . mail_user_rel
 * @package Compass\Federation
 */
class Struct_Db_LdapData_MailUserRel {

	public function __construct(
		public string $uid,
		public int    $mail_source,
		public string $mail,
		public int    $created_at,
		public int    $updated_at,
	) {
	}

	/**
	 * конвертируем запись в структуру
	 *
	 * @param array $row
	 *
	 * @return Struct_Db_LdapData_MailUserRel
	 */
	public static function rowToStruct(array $row):Struct_Db_LdapData_MailUserRel {

		return new Struct_Db_LdapData_MailUserRel(
			(string) $row["uid"],
			(int) $row["mail_source"],
			(string) $row["mail"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
		);
	}
}