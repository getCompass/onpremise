<?php

namespace Compass\Pivot;

/**
 * структура описывающая основные данные аккаунта LDAP
 * @package Compass\Pivot
 */
class Struct_User_Auth_Ldap_AccountData {

	public function __construct(
		public string $display_name,
		public string $uid,
		public string $username,
	) {
	}

	/**
	 * конвертируем ассоц. массив в структуру
	 *
	 * @return static
	 */
	public static function arrayToStruct(array $array):self {

		return new self(
			$array["display_name"],
			$array["uid"],
			$array["username"],
		);
	}
}