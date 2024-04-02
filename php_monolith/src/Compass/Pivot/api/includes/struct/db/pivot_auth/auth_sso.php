<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_auth_{Y}.auth_sso_list_{M}
 */
class Struct_Db_PivotAuth_AuthSso extends Struct_Db_PivotAuth_AuthDefault {

	/**
	 * Struct_Db_PivotAuth_AuthSso constructor.
	 */
	public function __construct(
		public string $auth_map,
		public string $sso_auth_token,
		public int    $created_at,
	) {
	}

	/**
	 * конвертируем запись в структуру
	 *
	 * @return Struct_Db_PivotAuth_AuthSso
	 */
	public static function rowToStruct(array $row):Struct_Db_PivotAuth_AuthSso {

		return new Struct_Db_PivotAuth_AuthSso(
			(string) $row["auth_map"],
			(string) $row["sso_auth_token"],
			(int) $row["created_at"],
		);
	}
}