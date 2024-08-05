<?php

namespace Compass\Pivot;

/**
 * класс-структура для таблицы pivot_auth_{Y}.auth_ldap_list_{M}
 */
class Struct_Db_PivotAuth_AuthLdap extends Struct_Db_PivotAuth_AuthDefault {

	/**
	 * Struct_Db_PivotAuth_AuthLdap constructor.
	 */
	public function __construct(
		public string $auth_map,
		public string $ldap_auth_token,
		public int    $created_at,
	) {
	}

	/**
	 * конвертируем запись в структуру
	 *
	 * @return Struct_Db_PivotAuth_AuthLdap
	 */
	public static function rowToStruct(array $row):Struct_Db_PivotAuth_AuthLdap {

		return new Struct_Db_PivotAuth_AuthLdap(
			(string) $row["auth_map"],
			(string) $row["ldap_auth_token"],
			(int) $row["created_at"],
		);
	}
}