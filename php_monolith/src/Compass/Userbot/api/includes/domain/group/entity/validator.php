<?php

namespace Compass\Userbot;

/**
 * класс для валидации данных сущности групп бота
 *
 * Class Domain_Group_Entity_Validator
 */
class Domain_Group_Entity_Validator {

	public const GET_GROUPS_MAX_COUNT = 300; // максимальное значение для count получения списка групп бота

	public const GET_GROUPS_COUNT_DEFAULT  = 100; // дефолт значение для count получения списка групп бота
	public const GET_GROUPS_OFFSET_DEFAULT = 0;   // дефолт значения для offset получения списка групп бота

	/**
	 * проверяем корректность ключа группы
	 *
	 * @throws \cs_Userbot_RequestIncorrect
	 */
	public static function assertGroupId(string $group_id):void {

		if (isEmptyString($group_id)) {
			throw new \cs_Userbot_RequestIncorrect("incorrect param group_id");
		}
	}

	/**
	 * проверяем корректность параметров для получения списка групп бота
	 *
	 * @throws \cs_Userbot_RequestIncorrect
	 */
	public static function assertParamsForGetGroups(int $count, int $offset):void {

		if ($count < 0 || $offset < 0) {
			throw new \cs_Userbot_RequestIncorrect("incorrect params count or offset");
		}

		if ($count > self::GET_GROUPS_MAX_COUNT) {
			throw new \cs_Userbot_RequestIncorrect("incorrect param count");
		}
	}
}