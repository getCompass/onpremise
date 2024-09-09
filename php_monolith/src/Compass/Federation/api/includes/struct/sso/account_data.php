<?php

namespace Compass\Federation;

/**
 * структура описывающая основные данные аккаунта SSO
 * @package Compass\Federation
 */
class Struct_Sso_AccountData {

	public function __construct(
		public ?string $name,
		public ?string $avatar,
		public ?string $badge,
		public ?string $role,
		public ?string $bio,
	) {
	}

	public function format():array {

		return [
			"name"   => $this->name,
			"avatar" => $this->avatar,
			"badge"  => $this->badge,
			"role"   => $this->role,
			"bio"    => $this->bio,
		];
	}
}