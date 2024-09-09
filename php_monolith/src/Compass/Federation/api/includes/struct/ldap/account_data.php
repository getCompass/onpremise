<?php

namespace Compass\Federation;

/**
 * структура описывающая основные данные аккаунта LDAP
 * @package Compass\Federation
 */
class Struct_Ldap_AccountData {

	public function __construct(
		public Struct_Sso_AccountData $account_data,
		public string                 $uid,
		public string                 $username,
	) {
	}

	public function format():array {

		$output             = $this->account_data->format();
		$output["uid"]      = (string) $this->uid;
		$output["username"] = (string) $this->username;

		return $output;
	}
}