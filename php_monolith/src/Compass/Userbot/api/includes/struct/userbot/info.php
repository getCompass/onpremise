<?php

namespace Compass\Userbot;

/**
 * класс-агрегат для информации бота
 */
class Struct_Userbot_Info {

	/**
	 * Struct_Userbot_Info constructor.
	 *
	 * @param string $userbot_id
	 * @param string $token
	 * @param int    $status
	 * @param string $company_url
	 * @param string $secret_key
	 * @param int    $is_react_command
	 * @param int    $userbot_user_id
	 * @param array  $extra
	 */
	public function __construct(
		public string $userbot_id,
		public string $token,
		public int $status,
		public string $company_url,
		public string $secret_key,
		public int $is_react_command,
		public int $userbot_user_id,
		public array $extra
	) {

	}
}
