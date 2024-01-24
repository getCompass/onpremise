<?php

namespace Compass\Company;

use BaseFrame\Conf\Emoji;

/**
 * Класс для фильтрации данных вводимых пользователем
 */
class Type_Api_Filter {

	public const MAX_LENGTH_COMMENT_TEXT = 2000;

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
		"\u{202C}",
		"\u{202D}",
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
	];

	// символы, которые необходимо конвертировать в перенос строки
	protected const _LINE_SEPARATOR_CHARS = [
		"\u{2028}",
	];

	/**
	 * Фильтруем текст
	 *
	 * @throws cs_Text_IsTooLong
	 */
	public static function prepareText(string $text, int $max_length):string {

		// бросаем исключение если длина текста больше максимального
		if (mb_strlen($text) > $max_length) {
			throw new cs_Text_IsTooLong(__CLASS__ . ": text is too long");
		}

		// удаляем весь левак
		$text = preg_replace("/([\r\n\f\v]){3,}/", "\n\n", $text);

		// удаляем не поддерживаемые символы
		$text = str_replace(self::_NOT_ALLOW_CHARS, "", $text);

		// конвертируем нестандартные символы переноса
		$text = str_replace(self::_LINE_SEPARATOR_CHARS, "\n", $text);

		// убираем пробелы с левого края
		$text = ltrim($text);

		// убираем пробелы с правого края
		$text = rtrim($text);

		// обрезаем
		return mb_substr($text, 0, $max_length);
	}

	// utf8 emoji -> :short_name:
	public static function replaceEmojiWithShortName(string $text):string {

		// получаем список всех смайликов из конфига
		$emoji_list = Emoji::EMOJI_LIST;

		// обрабатываем все смайлы
		$text = str_replace(array_keys($emoji_list), $emoji_list, $text);

		// обрабатываем флаги Отдельно потому что есть баг с флагами
		return self::_replaceFlagWithShortName($text);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// для флагов utf8 emoji -> :short_name:
	protected static function _replaceFlagWithShortName(string $text):string {

		// получаем список всех emoji-флагов из конфига
		$emoji_flag_list = Emoji::EMOJI_FLAG_LIST;

		// если флагов нет, то отдаем текст
		if (!preg_match_all("~[\u{1F1E6}\u{1F1E7}\u{1F1E8}\u{1F1E9}\u{1F1EA}\u{1F1EB}\u{1F1EC}\u{1F1ED}\u{1F1EE}\u{1F1EF}\u{1F1F0}\u{1F1F1}\u{1F1F2}\u{1F1F3}\u{1F1F4}\u{1F1F5}\u{1F1F6}\u{1F1F7}\u{1F1F8}\u{1F1F9}\u{1F1FA}\u{1F1FB}\u{1F1FC}\u{1F1FD}\u{1F1FE}\u{1F1FF}]{2}~ui", $text, $match)) {
			return $text;
		}

		// проходимся по каждому флагу
		foreach ($match[0] as $flag) {

			// заменяем его на :short_name:
			$text = preg_replace("/" . $flag . "/ui", $emoji_flag_list[$flag], $text, 1);
		}

		return $text;
	}
}