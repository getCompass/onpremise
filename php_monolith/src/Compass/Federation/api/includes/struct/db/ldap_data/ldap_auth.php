<?php

namespace Compass\Federation;

/**
 * класс описывает структуру сущности ldap_data . ldap_auth_list
 * @package Compass\Federation
 */
class Struct_Db_LdapData_LdapAuth {

	public function __construct(
		public string $ldap_auth_token,
		public int    $status,
		public int    $created_at,
		public int    $updated_at,
		public string $uid,
		public string $username,
		public string $dn,
		public array  $data,
	) {
	}

	/**
	 * конвертируем запись в структуру
	 *
	 * @return Struct_Db_LdapData_LdapAuth
	 */
	public static function rowToStruct(array $row):Struct_Db_LdapData_LdapAuth {

		return new Struct_Db_LdapData_LdapAuth(
			(string) $row["ldap_auth_token"],
			(int) $row["status"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
			(string) $row["uid"],
			(string) $row["username"],
			(string) $row["dn"],
			fromJson($row["data"]),
		);
	}
}