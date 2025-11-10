<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Структура для секьюрных данных пользовательского бота
 */
class Struct_Domain_Userbot_SensitiveData {

	/**
	 * Struct_Domain_Userbot_SensitiveData constructor.
	 */
	public function __construct(
		public string $token,
		public string $secret_key,
		public int    $is_react_command,
		public string $webhook,
		public array  $group_info_list,
		public int    $avatar_color_id,
		public string $avatar_file_key,
	) {

	}
}