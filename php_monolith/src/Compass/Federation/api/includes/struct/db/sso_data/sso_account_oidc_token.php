<?php

namespace Compass\Federation;

/**
 * класс описывает структуру сущности sso_data . auth_response_rel
 * @package Compass\Federation
 */
class Struct_Db_SsoData_SsoAccountOidcToken {

	public function __construct(
		public ?int                      $row_id,
		public string                    $sub_hash,
		public string                    $sso_auth_token,
		public int                       $expires_at,
		public int                       $last_refresh_at,
		public int                       $created_at,
		public int                       $updated_at,
		public Struct_Oidc_TokenResponse $data,
	) {
	}

	/**
	 * конвертируем запись в структуру
	 *
	 * @return Struct_Db_SsoData_SsoAccountOidcToken
	 */
	public static function rowToStruct(array $row):Struct_Db_SsoData_SsoAccountOidcToken {

		return new Struct_Db_SsoData_SsoAccountOidcToken(
			(int) $row["row_id"],
			(string) $row["sub_hash"],
			(string) $row["sso_auth_token"],
			(int) $row["expires_at"],
			(int) $row["last_refresh_at"],
			(int) $row["created_at"],
			(int) $row["updated_at"],
			Struct_Oidc_TokenResponse::arrayToStruct(fromJson($row["data"])),
		);
	}
}