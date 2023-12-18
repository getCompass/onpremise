<?php

namespace Compass\Company;

/**
 * Класс-агрегат для информации пользователя
 */
class Struct_User_Info {

	public int $avatar_color_id;

	/**
	 * Struct_User_Info constructor.
	 *
	 * @param int    $user_id
	 * @param string $full_name
	 * @param string $avatar_file_key
	 * @param int    $avatar_color_id
	 */
	public function __construct(
		public int    $user_id,
		public string $full_name,
		public string $avatar_file_key,
		int    $avatar_color_id,
	) {

		$this->avatar_color_id = $avatar_color_id === 0 ? \BaseFrame\Domain\User\Avatar::getColorByUserId($this->user_id) : $avatar_color_id;
	}
}
