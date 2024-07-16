<?php

namespace Compass\Jitsi;

/** структура распаршенной ссылки на конференцию */
class Struct_Jitsi_Conference_ParsedLink {

	public function __construct(
		public string $link,
		public string $conference_id,
		public string $password,
		public int    $creator_user_id,
	) {
	}
}