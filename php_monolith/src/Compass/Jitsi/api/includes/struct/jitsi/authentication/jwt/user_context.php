<?php

namespace Compass\Jitsi;

/** структура, описывающая контекст пользователя для jwt токена */
class Struct_Jitsi_Authentication_Jwt_UserContext {

	public function __construct(
		public string $name,
		public string $id,
		public string $email,
		public string $avatar,
		public string $type,
	) {
	}
}