<?php

namespace Compass\Company;

use BaseFrame\System\Character;

/**
 * Класс для очистки данных для приложения
 */
class Domain_SmartApp_Entity_Sanitizer {

	protected const _MAX_URL_LENGTH            = 1000; // максимальная длина для ссылки приложения
	protected const _MAX_SMART_APP_NAME_LENGTH = 40; // максимальная длина для smart_app_name
	protected const _MAX_TITLE_LENGTH          = 40; // максимальная длина title

	/**
	 * Очистка имени smart app
	 */
	public static function sanitizeSmartAppUniqName(string $smart_app_uniq_name):string {

		// приводим строку к нижнему регистру
		$smart_app_uniq_name = strtolower($smart_app_uniq_name);

		// удаляем все символы, кроме a-z и цифр 0-9
		$smart_app_uniq_name = preg_replace("/[^a-z0-9]/", "", $smart_app_uniq_name);

		// обрезаем
		return mb_substr($smart_app_uniq_name, 0, self::_MAX_SMART_APP_NAME_LENGTH);
	}

	/**
	 * Очистка title от лишних символов
	 *
	 * @param string $title
	 *
	 * @return string
	 */
	public static function sanitizeTitle(string $title):string {

		// удаляем все лишнее, кроме точки
		$special_character_regex = str_replace(".", "", Character::SPECIAL_CHARACTER_REGEX);
		$full_name               = trim(preg_replace([
			Character::EMOJI_REGEX,
			Character::COMMON_FORBIDDEN_CHARACTER_REGEX,
			$special_character_regex,
			Character::FANCY_TEXT_REGEX,
			Character::DOUBLE_SPACE_REGEX,
			Character::NEWLINE_REGEX,
		], ["", "", "", "", " ", ""], $title));

		// обрезаем
		return mb_substr($full_name, 0, self::_MAX_TITLE_LENGTH);
	}

	/**
	 * Очистка url приложения
	 */
	public static function sanitizeUrl(string $url):string {

		// удаляем весь левак
		$url = preg_replace("/[^\w _.\/\-$&+,:;~!'()*=?%\[\]@#]/uism", "", $url);

		// удаляем лишние пробелы
		$url = trim(preg_replace("/[ ]{2,}/", " ", $url));

		// обрезаем
		return mb_substr($url, 0, self::_MAX_URL_LENGTH);
	}
}