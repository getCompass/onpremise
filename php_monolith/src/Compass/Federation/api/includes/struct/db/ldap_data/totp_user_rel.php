<?php

namespace Compass\Federation;

/**
 * класс описывает структуру сущности ldap_data . ldap_totp_user_rel
 * @package Compass\Federation
 */
class Struct_Db_LdapData_TotpUserRel
{
	public function __construct(
		public string $uid,
		public string $crypted_totp_secret,
		public int $created_at,
		public int $updated_at,
	) {
	}

	/**
	 * конвертируем запись в структуру
	 */
	public static function rowToStruct(array $row): self
	{

		return new self(
			(string) $row["uid"],
			(string) $row["crypted_totp_secret"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
		);
	}
}
