<?php

namespace Compass\Conversation;

/**
 * Класс для подготовки запросов.
 */
class Domain_Search_Helper_Query_Sanitizer {

	/**
	 * Массив со списком букв и их замен. Используется в @see self::replaceSomeLetters()
	 * Формат: "<буква которую заменяем>" => "<буква на которому заменяем>"
	 */
	protected const _REPLACE_LETTER_LIST = [
		"ё" => "е",
	];

	/**
	 * Массив со списком символов и их замен. Используется в @see self::replaceSomeSymbols()
	 * Формат: "<символ который заменяем>" => "<символ на который заменяем>"
	 */
	protected const _REPLACE_SYMBOL_LIST = [
		"_" => " ",
	];

	/**
	 * Убирает из запроса управляющие последовательности.
	 */
	public static function clearControls(string $raw_query):string {

		return str_replace(["f!", "r!", "q!", "p!", "m!", "e!"], "", $raw_query);
	}

	/**
	 * Заменяем некоторые буквы
	 * Лучше всего использовать эту функцию в самом начале, прогоняя сырой поисковый запрос
	 *
	 * @return string
	 */
	public static function replaceSomeLetters(string $raw_query):string {

		foreach (self::_REPLACE_LETTER_LIST as $search => $replace) {

			$raw_query = str_replace($search, $replace, $raw_query);
		}

		return $raw_query;
	}

	/**
	 * Заменяем некоторые символы в строке
	 *
	 * @return string
	 */
	public static function replaceSomeSymbols(string $raw_query):string {

		foreach (self::_REPLACE_SYMBOL_LIST as $search => $replace) {

			$raw_query = str_replace($search, $replace, $raw_query);
		}

		return $raw_query;
	}
}
