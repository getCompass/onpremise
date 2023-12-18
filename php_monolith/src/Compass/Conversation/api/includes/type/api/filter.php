<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\System\Locale;

/**
 * класс для форматирования названия сущностей, строго по соглашениям
 */
class Type_Api_Filter {

	public const    MAX_MESSAGE_TEXT_LENGTH       = 2000; // максимальная длина сообщения
	public const    MAX_REMIND_TEXT_LENGTH        = 300;  // максимальная длина текста для Напоминания
	protected const _MAX_GROUP_NAME_LENGTH        = 40;   // максимальная длина имени группы
	protected const _MAX_CLIENT_MESSAGE_ID_LENGTH = 80;   // максимальная длина id сообщения на клиенте
	protected const _MAX_REASON_LENGTH            = 256;  // максимальная длина reason
	protected const _MAX_FILE_NAME_LENGTH         = 255;  // максимальная длина названия файла
	protected const _MAX_GROUP_DESCRIPTION_LENGTH = 500; // максимальная длина описания группы
	// регулярка для фильтрации названия группы
	protected const _GROUP_NAME_REGEXP = "/[^а-яёa-z0-9[:punct:] œẞßÄäÜüÖöÀàÈèÉéÌìÍíÎîÒòÓóÙùÚúÂâÊêÔôÛûËëÏïŸÿÇçÑñ¿¡]|[<>]/ui";

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

	// очищает название группового диалога
	public static function sanitizeGroupName(string $group_name):string {

		// обрезаем
		$group_name = mb_substr($group_name, 0, self::_MAX_GROUP_NAME_LENGTH);

		// удаляем лишнее
		return trim(preg_replace([self::_GROUP_NAME_REGEXP, "/[ ]{2,}/u"], ["", " "], $group_name));
	}

	/**
	 * Очищаем описание чата
	 *
	 * @param string $description
	 *
	 * @return string
	 */
	public static function sanitizeGroupDescription(string $description):string {

		// меняем emoji
		$status = self::replaceEmojiWithShortName($description);

		// удаляем лишнее
		$status = trim(preg_replace("/([\r\n\f\v]){3,}/", "\n\n", $status));

		// обрезаем
		return mb_substr($status, 0, self::_MAX_GROUP_DESCRIPTION_LENGTH);
	}

	// очищает текст сообщения
	public static function sanitizeMessageText(string $text, bool $is_ltrim = true, bool $is_rtrim = true):string {

		// удаляем лишнее
		$text = preg_replace("/([\r\n\f\v]){3,}/", "\n\n", $text);

		// удаляем не поддерживаемые символы
		$text = str_replace(self::_NOT_ALLOW_CHARS, "", $text);

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
		$client_message_id = preg_replace("/[^\w\-(){}]/uism", "", $client_message_id);

		return $client_message_id;
	}

	// фильтрует reason
	public static function sanitizeReason(string $reason):string {

		// обрезаем до макс длины
		$reason = mb_substr($reason, 0, self::_MAX_REASON_LENGTH);

		// удаляем лишнее
		$reason = preg_replace("/[^\w\-(){}]/uism", "", $reason);

		return $reason;
	}

	// utf8 emoji -> :short_name:
	public static function replaceEmojiWithShortName(string $text):string {

		// получаем список всех смайликов из конфига
		$emoji_list = \BaseFrame\Conf\Emoji::EMOJI_LIST;

		// обрабатываем все смайлы
		$text = str_replace(array_keys($emoji_list), $emoji_list, $text);

		// обрабатываем флаги Отдельно потому что есть баг с флагами
		$text = self::_replaceFlagWithShortName($text);

		return $text;
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

	/**
	 * процесс подмены алиасов замены, если таковые имеются в тексте
	 *
	 * @throws \parseException
	 */
	public static function processSubstitutionsIfExist(string $text, array $substitution_list):string {

		foreach ($substitution_list as $substitution) {
			$replace_text = self::_getSubstitutionTextForReplace($substitution);
			$text         = str_replace("%{$substitution}%", $replace_text, $text);
		}

		return $text;
	}

	/**
	 * получаем текст для замены
	 *
	 * @param string $substitution
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	protected static function _getSubstitutionTextForReplace(string $substitution):string {

		try {
			return match ($substitution) {

				"general_group_name" => Domain_Group_Entity_Company::getDefaultGroupNameByKey(
					Domain_Company_Entity_Config::GENERAL_CONVERSATION_KEY_NAME, Locale::getLocale()),

				"heroes_group_name" => Domain_Group_Entity_Company::getDefaultGroupNameByKey(
					Domain_Company_Entity_Config::HEROES_CONVERSATION_KEY_NAME, Locale::getLocale()),

				"challenge_group_name" => Domain_Group_Entity_Company::getDefaultGroupNameByKey(
					Domain_Company_Entity_Config::CHALLENGE_CONVERSATION_KEY_NAME, Locale::getLocale()),

				default => "Unknown",
			};
		} catch (LocaleTextNotFound) {
			throw new ParseFatalException("cant find group default name");
		}
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

			// заменяем его на :short_name:
			$text = preg_replace("/" . $flag . "/ui", $emoji_flag_list[$flag], $text, 1);
		}

		return $text;
	}
}