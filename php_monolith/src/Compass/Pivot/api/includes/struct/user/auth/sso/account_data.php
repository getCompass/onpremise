<?php

namespace Compass\Pivot;

/**
 * структура описывающая основные данные аккаунта SSO
 * @package Compass\Pivot
 */
class Struct_User_Auth_Sso_AccountData {

	public function __construct(
		public ?string $name,
		public ?string $avatar,
		public ?string $badge,
		public ?string $role,
		public ?string $bio,
		public string  $mail,
		public string  $phone_number,
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
			$array["mail"],
			$array["phone_number"],
		);
	}
}