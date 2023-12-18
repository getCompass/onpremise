<?php

namespace Compass\Userbot;

/**
 * Действие преобразования user_id
 *
 * Class Domain_Userbot_Action_ConvertServiceUserId
 */
class Domain_Userbot_Action_ConvertServiceUserId {

	/**
	 * от внешнего сервиса User-ID в обычный user_id
	 *
	 * @throws \cs_Userbot_RequestIncorrect
	 */
	public static function from(string $user_id):int {

		$array = explode("-", $user_id);

		if (count($array) != 2) {
			throw new \cs_Userbot_RequestIncorrect("passed incorrect value User-ID");
		}

		if (strtolower($array[0]) !== strtolower("User")) {
			throw new \cs_Userbot_RequestIncorrect("passed incorrect value User-ID");
		}

		return (int) $array[1];
	}

	/**
	 * обычный user_id для внешнего сервиса User-ID
	 */
	public static function to(int $user_id):string {

		return "User-{$user_id}";
	}
}