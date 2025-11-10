<?php

namespace Compass\Thread;

/**
 * класс для форматирования названия сущностей, строго по соглашениям
 */
class Type_Api_Filter {

	public const    MAX_MESSAGE_TEXT_LENGTH       = 2000; // максимальная длина сообщения
	public const    MAX_REMIND_TEXT_LENGTH        = 300; // максимальная длина комментария для Напоминания
	protected const _MAX_CLIENT_MESSAGE_ID_LENGTH = 80;   // максимальная длина id сообщения на клиенте
	protected const _MAX_REASON_LENGTH            = 256;  // максимальная длина reason
	protected const _MAX_FILE_NAME_LENGTH         = 255; // максимальная длина названия файла

	// массив не поддерживаемых символов
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

	// список неподдерживаемых символов
	protected const _NOT_ALLOW_CHARS_NEW = [
		"\u{202C}",
		"\u{202D}",
	];

	// символы, которые необходимо конвертировать в перенос строки
	protected const _LINE_SEPARATOR_CHARS = [
		"\u{2028}",
	];

	// очищает текст сообщения
	public static function sanitizeMessageText(string $text, bool $is_ltrim = true, bool $is_rtrim = true):string {

		// удаляем лишнее
		$text = preg_replace("/([\r\n\f\v]){3,}/", "\n\n", $text);

		// удаляем не поддерживаемые символы
		$text = str_replace(self::_NOT_ALLOW_CHARS, "", $text);

		$text = str_replace(self::_NOT_ALLOW_CHARS_NEW, "", $text);

		// конвертируем нестандартные символы переноса
		$text = str_replace(self::_LINE_SEPARATOR_CHARS, "\n", $text);

		// убираем пробелы с левого края
		if ($is_ltrim) {
			$text = ltrim($text);
		}

		// убираем пробелы с правого края
		if ($is_rtrim) {
			$text = rtrim($text);
		}

		// обрезаем
		$text = mb_substr($text, 0, self::MAX_MESSAGE_TEXT_LENGTH);
		return $text;
	}

	// фильтрует client_message_id
	public static function sanitizeClientMessageId(string $client_message_id):string {

		// обрезаем до макс длины
		$client_message_id = mb_substr($client_message_id, 0, self::_MAX_CLIENT_MESSAGE_ID_LENGTH);

		// удаляем лишнее
		$client_message_id = preg_replace("/[^\w\-\(\)\{\}]/uism", "", $client_message_id);

		return $client_message_id;
	}

	// фильтруем file_name
	public static function sanitizeFileName(string $file_name):string {

		// удаляем лишнее
		$file_name = preg_replace("/([\r\n\f\v]){3,}/", "\n\n", $file_name);

		// удаляем не поддерживаемые символы
		$file_name = str_replace(self::_NOT_ALLOW_CHARS, "", $file_name);

		// убираем пробелы по краям
		$file_name = trim($file_name);

		// обрезаем
		$file_name = mb_substr($file_name, 0, self::_MAX_FILE_NAME_LENGTH);
		$file_name = Type_Api_Filter::replaceEmojiWithShortName($file_name);

		return $file_name;
	}

	// фильтрует reason
	public static function sanitizeReason(string $reason):string {

		// обрезаем до макс длины
		$reason = mb_substr($reason, 0, self::_MAX_REASON_LENGTH);

		// удаляем лишнее
		$reason = preg_replace("/[^\w\-\(\)\{\}]/uism", "", $reason);

		return $reason;
	}

	// удаляем лишнее из поискового запроса
	public static function prepareSearchQuery(string $query):string {

		// экранируем символы и добавляем звездочки
		return "*" . addcslashes($query, "\\()|-!@~\"&/^$=") . "*";
	}

	// utf8 emoji -> :short_name:
	public static function replaceEmojiWithShortName(string $text):string {

		// получаем список всех смайликов из конфига
		$emoji_list = \BaseFrame\Conf\Emoji::EMOJI_LIST;

		// обрабатываем все смайлы
		$text = str_replace(array_keys($emoji_list), $emoji_list, $text);

		// обрабатываем флаги Отдельно потому что есть баг с флагами
		// отдаем ответ
		return self::_replaceFlagWithShortName($text);
	}

	// преобразовываем :short_name: -> utf8 emoji
	public static function replaceShortNameToEmoji(string $text):string {

		// получаем список смайлов из конфига
		$emoji_list                  = \BaseFrame\Conf\Emoji::EMOJI_LIST;
		$emoji_flag_list             = \BaseFrame\Conf\Emoji::EMOJI_FLAG_LIST;
		$emoji_alias_short_name_list = \BaseFrame\Conf\Emoji::EMOJI_ALIAS_SHORT_NAME_LIST;

		$emoji_flag_list = array_flip($emoji_flag_list);
		$emoji_list      = array_flip($emoji_list);

		// меняем короткие имена эмоций
		$text = str_replace(array_keys($emoji_list), $emoji_list, $text);

		// меняем короткие имена флагов
		$text = str_replace(array_keys($emoji_flag_list), $emoji_flag_list, $text);

		// меняем алиасы шортнеймов эмоций
		return str_replace(array_keys($emoji_alias_short_name_list), $emoji_alias_short_name_list, $text);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// для флагов utf8 emoji -> :short_name:
	protected static function _replaceFlagWithShortName(string $text):string {

		// получаем список всех смайликов из конфига
		$emoji_flag_list = \BaseFrame\Conf\Emoji::EMOJI_FLAG_LIST;

		// если флагов нет, то отдаем текст
		if (!preg_match_all("~[\u{1F1E6}\u{1F1E7}\u{1F1E8}\u{1F1E9}\u{1F1EA}\u{1F1EB}\u{1F1EC}\u{1F1ED}\u{1F1EE}\u{1F1EF}\u{1F1F0}\u{1F1F1}\u{1F1F2}\u{1F1F3}\u{1F1F4}\u{1F1F5}\u{1F1F6}\u{1F1F7}\u{1F1F8}\u{1F1F9}\u{1F1FA}\u{1F1FB}\u{1F1FC}\u{1F1FD}\u{1F1FE}\u{1F1FF}]{2}~ui", $text, $match)) {
			return $text;
		}

		// проходимся по каждому флагу
		foreach ($match[0] as $flag) {

			$emoji_flag = $emoji_flag_list[$flag] ?? "";

			// заменяем его на :short_name:, если возможно
			$text = preg_replace("/" . $flag . "/ui", $emoji_flag, $text, 1);
		}

		return $text;
	}
}