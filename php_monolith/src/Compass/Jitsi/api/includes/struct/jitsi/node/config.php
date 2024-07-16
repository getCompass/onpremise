<?php

namespace Compass\Jitsi;

/** структура описывающий конфиг jitsi ноды */
class Struct_Jitsi_Node_Config {

	public function __construct(
		public string $domain,
		public string $subdir,
		public string $jwt_secret,
		public string $jwt_issuer,
		public string $jwt_audience,
		public string $event_auth_token,
		public string $rest_api_auth_token,
	) {
	}
}