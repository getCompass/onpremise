<?php

namespace Compass\Federation;

/**
 * класс описывает структуру сущности sso_data . sso_auth_list
 * @package Compass\Federation
 */
class Struct_Db_SsoData_SsoAuth {

	public function __construct(
		public string $sso_auth_token,
		public string $signature,
		public int    $status,
		public int    $expires_at,
		public int    $completed_at,
		public int    $created_at,
		public int    $updated_at,
		public string $link,
		public string $ua_hash,
		public string $ip_address,
	) {
	}

	/**
	 * конвертируем запись в структуру
	 *
	 * @return Struct_Db_SsoData_SsoAuth
	 */
	public static function rowToStruct(array $row):Struct_Db_SsoData_SsoAuth {

		return new Struct_Db_SsoData_SsoAuth(
			(string) $row["sso_auth_token"],
			(string) $row["signature"],
			(int) $row["status"],
			(int) $row["expires_at"],
			(int) $row["completed_at"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
			(string) $row["link"],
			(string) $row["ua_hash"],
			(string) $row["ip_address"],
		);
	}
}