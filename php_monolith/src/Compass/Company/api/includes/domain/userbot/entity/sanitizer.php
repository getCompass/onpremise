<?php

namespace Compass\Company;

/**
 * Класс для очистки данных для бота
 */
class Domain_Userbot_Entity_Sanitizer {

	protected const _MAX_WEBHOOK_URL_LENGTH    = 1000; // максимальная длина для вебхука бота
	protected const _MAX_NAME_LENGTH        = 40;   // максимальная длина имени

	/**
	 * очистка вебхука бота
	 */
	public static function sanitizeWebhookUrl(string $webhook_url):string {

		// удаляем весь левак
		$webhook_url = preg_replace("/[^\w _.\/\-$&+,:;~!'()*=?%\[\]@#]/uism", "", $webhook_url);

		// удаляем лишние пробелы
		$webhook_url = trim(preg_replace("/[ ]{2,}/", " ", $webhook_url));

		// обрезаем
		return mb_substr($webhook_url, 0, self::_MAX_WEBHOOK_URL_LENGTH);
	}

	/**
	 * очистка команды бота
	 */
	public static function sanitizeCommand(string $command):string {

		// удаляем весь левак
		$command = preg_replace("/[^\w _.\/\-$+,:;=?@#\]\[]/uism", "", $command);

		// удаляем лишние пробелы
		$command = trim(preg_replace("/[ ]{2,}/", " ", $command));

		// обрезаем
		return mb_substr($command, 0, Domain_Userbot_Entity_Validator::COMMAND_LENGTH_MAX_LIMIT);
	}

	/**
	 * Очистка имени от лишних символов
	 *
	 * @param string $full_name
	 *
	 * @return string
	 */
	public static function sanitizeName(string $full_name):string {

		// если текст состоит только из символов
		if (preg_match("/^[^[:alnum:]]+$/u", $full_name)) {

			return "";
		}

		// удаляем лишнее
		$full_name = \BaseFrame\System\Character::sanitizeFullForbiddenCharacterRegex($full_name);

		// обрезаем
		return mb_substr($full_name, 0, self::_MAX_NAME_LENGTH);
	}
}