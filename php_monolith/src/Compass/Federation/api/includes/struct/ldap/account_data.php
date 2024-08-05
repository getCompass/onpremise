<?php

namespace Compass\Federation;

/**
 * структура описывающая основные данные аккаунта LDAP
 * @package Compass\Federation
 */
class Struct_Ldap_AccountData {

	public function __construct(
		public string $display_name,
		public string $uid,
		public string $username,
	) {
	}

	public function format():array {

		return [
			"display_name" => (string) $this->display_name,
			"uid"          => (string) $this->uid,
			"username"     => (string) $this->username,
		];
	}
}