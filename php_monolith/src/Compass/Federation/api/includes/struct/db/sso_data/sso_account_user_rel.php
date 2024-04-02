<?php

namespace Compass\Federation;

/**
 * класс описывает структуру сущности sso_data . sso_account_user_rel
 * @package Compass\Federation
 */
class Struct_Db_SsoData_SsoAccountUserRel {

	public function __construct(
		public string $sub_hash,
		public int    $user_id,
		public string $sub_plain,
		public int    $created_at,
	) {
	}

	/**
	 * конвертируем запись в структуру
	 *
	 * @return Struct_Db_SsoData_SsoAccountUserRel
	 */
	public static function rowToStruct(array $row):Struct_Db_SsoData_SsoAccountUserRel {

		return new Struct_Db_SsoData_SsoAccountUserRel(
			(string) $row["sub_hash"],
			(int) $row["user_id"],
			(string) $row["sub_plain"],
			(int) $row["created_at"],
		);
	}
}