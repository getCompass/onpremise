<?php

namespace Compass\Federation;

/**
 * класс описывает структуру сущности ldap_data . ldap_account_user_rel
 * @package Compass\Federation
 */
class Struct_Db_LdapData_LdapAccountUserRel {

	public function __construct(
		public string $uid,
		public int    $user_id,
		public int    $status,
		public int    $created_at,
		public int    $updated_at,
		public string $username,
		public string $dn,
	) {
	}

	/**
	 * конвертируем запись в структуру
	 *
	 * @return Struct_Db_LdapData_LdapAccountUserRel
	 */
	public static function rowToStruct(array $row):Struct_Db_LdapData_LdapAccountUserRel {

		return new Struct_Db_LdapData_LdapAccountUserRel(
			(string) $row["uid"],
			(int) $row["user_id"],
			(int) $row["status"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
			(string) $row["username"],
			(string) $row["dn"],
		);
	}
}