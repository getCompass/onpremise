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
	 * проверяем корректность avatar_file_key
	 *
	 * @throws Domain_Userbot_Exception_IncorrectParam
	 */
	public static function assertCorrectAvatarFileKey(string|false $avatar_file_key):void {

		if ($avatar_file_key !== false && mb_strlen($avatar_file_key) < 1) {
			throw new Domain_Userbot_Exception_IncorrectParam("incorrect avatar_file_key = {$avatar_file_key}");
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
	 * проверяем корректность is_smart_app
	 *
	 * @throws Domain_Userbot_Exception_IncorrectParam
	 */
	public static function assertCorrectFlagSmartApp(int $is_smart_app):void {

		if (!in_array($is_smart_app, [0, 1])) {
			throw new Domain_Userbot_Exception_IncorrectParam("incorrect is_smart_app = {$is_smart_app}");
		}
	}

	/**
	 * проверяем корректность размеры стороны smart_app
	 *
	 * @throws Domain_Userbot_Exception_IncorrectParam
	 */
	public static function assertCorrectSmartAppSideResolution(int $smart_app_side_resolution):void {

		if ($smart_app_side_resolution < 1 || $smart_app_side_resolution > 2560) {
			throw new Domain_Userbot_Exception_IncorrectParam("incorrect smart_app_side_resolution = {$smart_app_side_resolution}");
		}
	}

	/**
	 * проверяем корректность smart app name
	 *
	 * @throws Domain_Userbot_Exception_EmptyWebhook
	 */
	public static function assertCorrectSmartAppName(string $smart_app_name):void {

		if (isEmptyString($smart_app_name)) {
			throw new Domain_Userbot_Exception_EmptySmartAppName("empty smart_app_name");
		}
	}

	/**
	 * проверяем что smart app name уникальный в рамках команды
	 *
	 * @throws Domain_Userbot_Exception_EmptyWebhook
	 */
	public static function assertUniqSmartAppName(string $smart_app_name):void {

		// в случае очистки name ничего не делаем
		if ($smart_app_name === "") {
			return;
		}

		try {
			Gateway_Db_CompanyData_UserbotList::getBySmartAppName($smart_app_name);
		} catch (Domain_Userbot_Exception_UserbotNotFound) {

			// если не нашли, значит имя уникальное
			return;
		}

		throw new Domain_Userbot_Exception_NotUniqSmartAppName("incorrect smart_app_name");
	}

	/**
	 * проверяем корректность smart app url
	 *
	 * @throws Domain_Userbot_Exception_EmptyWebhook
	 */
	public static function assertCorrectSmartAppUrl(string $smart_app_url):void {

		if (isEmptyString($smart_app_url)) {
			throw new Domain_Userbot_Exception_EmptySmartAppUrl("empty smart_app_url");
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