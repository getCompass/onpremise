<?php

namespace Compass\Jitsi;

/** Класс для валидации сущностей конференции */
class Domain_Jitsi_Entity_validator {

	protected const _MAX_DESCRIPTION_LENGTH = 40;        // максимальная длина названия конференции
	protected const _MAX_LINK_LENGTH        = 40;        // максимальная длина ссылки

	protected const _DESCRIPTION_REGEXP                = "/|[[:punct:]<>]/ui";
	protected const _CONFERENCE_URL_CUSTOM_NAME_REGEXP = "/[^a-z0-9]|/ui";

	// список неподдерживаемых символов
	protected const _NOT_ALLOW_CHARS = [
		"\u{2000}",
		"\u{2001}",
		"\u{2002}",
		"\u{2003}",
		"\u{2004}",
		"\u{2005}",
		"\u{2006}",
		"\u{2007}",
		"\u{2008}",
		"\u{2009}",
		"\u{200A}",
		"\u{200B}",
		"\u{200C}",
		"\u{200D}",
		"\u{200E}",
		"\u{200F}",
		"\u{2060}",
		"\u{2061}",
		"\u{2062}",
		"\u{2063}",
		"\u{2064}",
		"\u{2065}",
		"\u{2066}",
		"\u{2067}",
		"\u{2068}",
		"\u{2069}",
		"\u{206A}",
		"\u{206B}",
		"\u{206C}",
		"\u{206D}",
		"\u{206E}",
		"\u{206F}",
		"\u{FFF0}",
		"\u{FFF1}",
		"\u{FFF2}",
		"\u{FFF3}",
		"\u{FFF4}",
		"\u{FFF5}",
		"\u{FFF6}",
		"\u{FFF7}",
		"\u{FFF8}",
		"\u{FFF9}",
		"\u{FFFA}",
		"\u{FFFB}",
		"\u{FFFC}",
		"\u{FFFD}",
		"\u{FFFE}",
		"\u{FFFF}",
		"\u{202C}",
		"\u{202D}",
	];

	// символы, которые необходимо конвертировать в перенос строки
	protected const _LINE_SEPARATOR_CHARS = [
		"\u{2028}",
	];

	/**
	 * Проверяем валидность названия конференции
	 */
	public static function isCorrectDescription(string $description):bool {

		if (self::isStringContainEmoji($description)) {
			return false;
		}

		if (mb_strlen($description) < 1 || mb_strlen($description) > self::_MAX_DESCRIPTION_LENGTH) {
			return false;
		}

		return true;
	}

	/**
	 * Проверяем валидность уникальной части ссылки конференции
	 */
	public static function isCorrectConferenceUrlCustomName(string $conference_url_custom_name):bool {

		if (mb_strlen($conference_url_custom_name) < 1 || mb_strlen($conference_url_custom_name) > self::_MAX_LINK_LENGTH) {
			return false;
		}

		return true;
	}

	/**
	 * Проверяем, есть ли эмодзи
	 */
	public static function isStringContainEmoji(string $input_string):bool {

		// получаем список всех смайликов из конфига
		$emoji_list = \BaseFrame\Conf\Emoji::EMOJI_LIST;

		// обрабатываем все эмоджи
		$tmp = str_replace(array_keys($emoji_list), $emoji_list, $input_string);

		// если есть изменения
		if ($tmp !== $input_string) {
			return true;
		}

		// получаем список флагов эмоджи
		$emoji_list = \BaseFrame\Conf\Emoji::EMOJI_FLAG_LIST;

		// обрабатываем все флаги эмоджи
		$tmp = str_replace(array_keys($emoji_list), $emoji_list, $input_string);

		// если есть изменения
		if ($tmp !== $input_string) {
			return true;
		}

		return false;
	}

	/**
	 * Проверяем space_id на корректность
	 */
	public static function isCorrectSpaceId(int $space_id):bool {

		if ($space_id < 1) {
			return false;
		}

		return true;
	}

	/**
	 * Очистка описания конференции от лишних символов
	 */
	public static function sanitizeDescription(string $full_name):string {

		// удаляем лишнее
		$full_name = trim(preg_replace([self::_DESCRIPTION_REGEXP, "/[ ]{2,}/u"], ["", " "], $full_name));
		return str_replace([...self::_NOT_ALLOW_CHARS, ...self::_LINE_SEPARATOR_CHARS], ["", "\n"], $full_name);
	}

	/**
	 * Очистка ссылки конференции от лишних символов
	 */
	public static function sanitizeConferenceUrlCustomName(string $conference_url_custom_name):string {

		$conference_url_custom_name = mb_strtolower($conference_url_custom_name);

		return trim(preg_replace([self::_CONFERENCE_URL_CUSTOM_NAME_REGEXP, "/[ ]{2,}/u"], ["", " "], $conference_url_custom_name));
	}
}