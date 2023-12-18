<?php

namespace Compass\Userbot;

/**
 * класс для валидации данных сущности пользователя для бота
 *
 * Class Domain_User_Entity_Validator
 */
class Domain_User_Entity_Validator {

	public const GET_USERS_MAX_COUNT = 300; // максимальное значение для count получения списка пользователей для бота

	public const GET_USERS_COUNT_DEFAULT  = 100; // дефолт значение для count получения списка пользователей для бота
	public const GET_USERS_OFFSET_DEFAULT = 0;   // дефолт значения для offset получения списка пользователей для бота

	/**
	 * проверяем корректность параметров для получения списка пользователей для бота
	 *
	 * @throws \cs_Userbot_RequestIncorrect
	 */
	public static function assertParamsForGetUsers(int $count, int $offset):void {

		if ($count < 0 || $offset < 0) {
			throw new \cs_Userbot_RequestIncorrect("passed incorrect params");
		}

		if ($count > self::GET_USERS_MAX_COUNT) {
			throw new \cs_Userbot_RequestIncorrect("passed incorrect params");
		}
	}
}