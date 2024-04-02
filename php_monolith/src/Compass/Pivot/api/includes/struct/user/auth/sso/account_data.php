<?php

namespace Compass\Pivot;

/**
 * структура описывающая основные данные аккаунта SSO
 * @package Compass\Pivot
 */
class Struct_User_Auth_Sso_AccountData {

	public function __construct(
		public string $first_name,
		public string $last_name,
		public string $mail,
		public string $phone_number,
	) {
	}

	/**
	 * конвертируем ассоц. массив в структуру
	 *
	 * @return static
	 */
	public static function arrayToStruct(array $array):self {

		return new self(
			$array["first_name"],
			$array["last_name"],
			$array["mail"],
			$array["phone_number"],
		);
	}
}