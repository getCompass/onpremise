<?php

namespace Compass\Userbot;

/**
 * класс для валидации данных сущности команд
 *
 * Class Domain_Command_Entity_Validator
 */
class Domain_Command_Entity_Validator {

	public const COMMAND_LIST_LIMIT = 100; // максимальное количество команд для бота

	public const COMMAND_LENGTH_MIN_LIMIT = 2;  // минимальная длина команды боты
	public const COMMAND_LENGTH_MAX_LIMIT = 80; // максимальная длина команды боты

	/**
	 * проверяем корректность списка команд бота
	 *
	 * @throws \cs_Userbot_RequestIncorrect
	 */
	public static function assertCorrectCommandsLimit(array $command_list):void {

		if (count($command_list) > self::COMMAND_LIST_LIMIT) {
			throw new \cs_Userbot_RequestIncorrect("exceeded limit for command_list");
		}
	}

	/**
	 * проверяем корректность длины каждой из команд
	 *
	 * @throws \cs_Userbot_RequestIncorrect
	 */
	public static function assertCorrectCommandLength(array $command_list):void {

		foreach ($command_list as $command) {

			if (mb_strlen($command) < self::COMMAND_LENGTH_MIN_LIMIT) {
				throw new \cs_Userbot_RequestIncorrect("command length is too small");
			}

			if (mb_strlen($command) > self::COMMAND_LENGTH_MAX_LIMIT) {
				throw new \cs_Userbot_RequestIncorrect("command length is too long");
			}
		}
	}

	/**
	 * проверяем, что команды в списке не совпадают
	 *
	 * @throws \cs_Userbot_RequestIncorrect
	 */
	public static function assertCommandDuplicate(array $command_list):void {

		if (count(array_unique($command_list)) != count($command_list)) {
			throw new \cs_Userbot_RequestIncorrect("duplicate in command list");
		}
	}
}