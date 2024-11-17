<?php

namespace Compass\Company;

/**
 * Класс для валидации данных для бота
 */
class Domain_Userbot_Entity_Validator {

	public const COMMAND_LENGTH_MIN_LIMIT = 2;   // минимальная длина команды боты
	public const COMMAND_LIST_LIMIT       = 100; // максимальное количество команд для бота
	public const COMMAND_LENGTH_MAX_LIMIT = 80;  // максимальная длина для команды бота

	/**
	 * проверяем корректность avatar_color_id
	 *
	 * @throws Domain_Userbot_Exception_IncorrectParam
	 */
	public static function assertCorrectAvatarColorId(int $avatar_color_id):void {

		if (!in_array($avatar_color_id, Domain_Userbot_Entity_Userbot::ALLOWED_AVATAR_COLOR_ID)) {
			throw new Domain_Userbot_Exception_IncorrectParam("incorrect avatar_color_id = {$avatar_color_id}");
		}
	}

	/**
	 * проверяем корректность is_react_command
	 *
	 * @throws Domain_Userbot_Exception_IncorrectParam
	 */
	public static function assertCorrectFlagReactCommand(int $is_react_command):void {

		if (!in_array($is_react_command, [0, 1])) {
			throw new Domain_Userbot_Exception_IncorrectParam("incorrect is_react_command = {$is_react_command}");
		}
	}

	/**
	 * проверяем корректность вебхука
	 *
	 * @throws Domain_Userbot_Exception_EmptyWebhook
	 */
	public static function assertCorrectWebhook(string $webhook):void {

		if (isEmptyString($webhook)) {
			throw new Domain_Userbot_Exception_EmptyWebhook("empty webhook");
		}
	}

	/**
	 * проверяем корректность списка команд бота
	 *
	 * @throws Domain_Userbot_Exception_IncorrectParam
	 */
	public static function assertCorrectCommandsLimit(array $command_list):void {

		if (count($command_list) > self::COMMAND_LIST_LIMIT) {
			throw new Domain_Userbot_Exception_IncorrectParam("incorrect param command_list");
		}
	}

	/**
	 * проверяем корректность длины каждой из команд
	 *
	 * @throws Domain_Userbot_Exception_IncorrectParam
	 */
	public static function assertCorrectCommandLength(array $command_list):void {

		foreach ($command_list as $command) {

			if (mb_strlen($command) < self::COMMAND_LENGTH_MIN_LIMIT) {
				throw new Domain_Userbot_Exception_IncorrectParam("command length is too small");
			}

			if (mb_strlen($command) > self::COMMAND_LENGTH_MAX_LIMIT) {
				throw new Domain_Userbot_Exception_IncorrectParam("command length is too long");
			}
		}
	}
}