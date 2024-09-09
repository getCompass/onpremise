<?php

namespace Compass\Federation;

/**
 * структура описывающая основные данные аккаунта SSO OIDC
 * @package Compass\Federation
 */
class Struct_Oidc_AccountData {

	public function __construct(
		public Struct_Sso_AccountData $account_data,
		public string                 $mail,
		public string                 $phone_number,
	) {
	}

	public function format():array {

		$output                 = $this->account_data->format();
		$output["mail"]         = (string) $this->mail;
		$output["phone_number"] = (string) $this->phone_number;

		return $output;
	}
}