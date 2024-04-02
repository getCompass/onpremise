<?php

namespace Compass\Federation;

/**
 * структура описывающая основные данные аккаунта SSO
 * @package Compass\Federation
 */
class Struct_Sso_AccountData {

	public function __construct(
		public string $first_name,
		public string $last_name,
		public string $mail,
		public string $phone_number,
	) {
	}

	public function format():array {

		return [
			"first_name"   => (string) $this->first_name,
			"last_name"    => (string) $this->last_name,
			"mail"         => (string) $this->mail,
			"phone_number" => (string) $this->phone_number,
		];
	}
}