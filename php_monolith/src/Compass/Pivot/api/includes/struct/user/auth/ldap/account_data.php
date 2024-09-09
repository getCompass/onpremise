<?php

namespace Compass\Pivot;

/**
 * структура описывающая основные данные аккаунта LDAP
 * @package Compass\Pivot
 */
class Struct_User_Auth_Ldap_AccountData {

	public function __construct(
		public ?string $name,
		public ?string $avatar,
		public ?string $badge,
		public ?string $role,
		public ?string $bio,
		public string  $uid,
		public string  $username,
	) {
	}

	/**
	 * конвертируем ассоц. массив в структуру
	 *
	 * @return static
	 */
	public static function arrayToStruct(array $array):self {

		return new self(
			$array["name"],
			$array["avatar"],
			$array["badge"],
			$array["role"],
			$array["bio"],
			$array["uid"],
			$array["username"],
		);
	}
}