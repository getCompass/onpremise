<?php

namespace Compass\Conversation;

/**
 * класс описывает действие по подсвечиванию совпадений в тексте
 */
class Domain_Search_Helper_Highlight {

	protected static float $_total_spent_time = 0;

	/**
	 * дефолтная разметка подсветки совпадения
	 * пример: Повседневная ++практика показывает++, что постоянный количественный рост
	 */
	public const HIGHLIGH_MARKUP_DEFAULT = [
		"start" => "++",
		"end"   => "++",
	];

	/**
	 * выполняем действие
	 *
	 * @param string   $source_text      исходный текст, в котором подсвечиваем совпадения
	 * @param string   $query            запрос, совпадения которого нужно подсветить в исходном тексте
	 *                                   ВАЖНО! Здесь должен быть исходный поисковый запрос, без лишних подготовок
	 *                                   всю необходимую подготовку сделаем в текущей функции
	 * @param string[] $locale_list      список локалей, для которых будем получать основу слов
	 *                                   основная задумка в том, чтобы сюда передавалась локаль пространства и англоязычная локаль
	 * @param array    $highlight_markup разметка, используемая для форматирования подсвечиваемого текста
	 *
	 * @return string
	 * @long
	 * @throws \Exception
	 */
	public static function highlight(string $source_text, string $query, array $locale_list, array $highlight_markup = self::HIGHLIGH_MARKUP_DEFAULT):array {

		$start_at = microtime(true);

		// переводим все shortname в эмодзи
		$source_text = Type_Api_Filter::replaceShortNameToEmoji($source_text);

		// подготавливаем текст поискового запроса
		$query = self::_prepareText($query);

		// подготавливаем исходный текст
		$source_text = self::_prepareText($source_text);

		// переводим эмодзи в shortname обратно
		$source_text = Type_Api_Filter::replaceEmojiWithShortName($source_text);

		// сохраним этот текст отдельно, чтобы вернуть в ответе
		$cleared_source_text = $source_text;

		// делаем стемминг поискового запроса
		$stemming_query = Domain_Search_Helper_Stemmer::stemText($query, $locale_list);

		// разбиваем исходный текст по словам
		$source_text_words = splitTextIntoWords($source_text);

		// разбиваем поисковый запрос по словам
		$stemming_query_words = splitTextIntoWords($stemming_query);

		// реверсим слова поискового запроса, поскольку isset O(1) быстрее нежели in_array O(n)
		$stemming_query_words_map = array_flip($stemming_query_words);

		// сюда будем складывать все уже подсвеченные слова, чтобы не подсвечивать их дважды
		$output_text = "";

		// список слов, которые в итоге подсветили
		$highlighted_list = [];

		// пробегаемся по каждому слову исходного текста
		foreach ($source_text_words as $word) {

			// оставляем от слова только буквы
			$processed_word = filterLetter($word);

			// если после фильтрации осталась пустая строка, то добавляем к ответу исходную строку
			if ($processed_word === "") {

				// оставляем часть слова в том виде в котором он существует в тексте (тут вероятнее всего какой-то символ/знак препинания/тп)
				$output_text .= $word;
				continue;
			}

			// делаем стемминг исходного слова
			$stemming_word = Domain_Search_Helper_Stemmer::stemText($processed_word, $locale_list);

			// ищем совпадение, если не нашли, то ничего не делаем со словом
			if (!self::_isMatches($stemming_word, $stemming_query_words_map)) {

				// оставляем слово в том виде в котором он существует в тексте
				$output_text .= $word;
				continue;
			}

			// подсвечиваем слово
			$highlighted_word   = self::_highlightString($processed_word, $highlight_markup);
			$highlighted_list[] = $processed_word;

			// добавляем к ответу
			$output_text .= $highlighted_word;
		}

		// возвращаем ответ из полученных слов
		static::$_total_spent_time += microtime(true) - $start_at;
		return [$cleared_source_text, $output_text, $highlighted_list];
	}

	/**
	 * Подготавливаем строку к выделению совпадений. Через функцию проходят обе строки (исходный текст; текст поискового запроса)
	 *
	 * @return string
	 */
	protected static function _prepareText(string $text):string {

		// очищаем строку от форматирования
		$text = Domain_Search_Helper_FormattingCleaner::clear($text);

		// к этому символу особая неприязнь? :D
		$text = trim($text, "_");

		// заменяем символы в строке
		return Domain_Search_Helper_Query_Sanitizer::replaceSomeSymbols($text);
	}

	/**
	 * Совпадает ли слово с любым из запроса
	 *
	 * @return bool
	 */
	protected static function _isMatches(string $stemming_source_word, array $stemming_query_words_map):bool {

		// если есть совпадение по основе слова
		if (isset($stemming_query_words_map[$stemming_source_word])) {
			return true;
		}

		// пробегаемся по всем словам из поискового запроса
		foreach ($stemming_query_words_map as $stemming_query_word => $_) {

			// если нашли совпадение в начале слова
			if (str_starts_with($stemming_source_word, $stemming_query_word)) {
				return true;
			}
		}

		// если дошли сюда, то значит ничего не нашли
		return false;
	}

	/**
	 * "подсвечиваем" строку с помощью тэга
	 *
	 * @return string
	 */
	protected static function _highlightString(string $string, array $highlight_tag):string {

		return "{$highlight_tag["start"]}{$string}{$highlight_tag["end"]}";
	}

	/**
	 * Записывает метрики времени исполнения.
	 */
	public static function writeMetrics():void {

		\BaseFrame\Monitor\Core::metric("highlight_work_time_ms", (int) (static::$_total_spent_time * 1000))->seal();
		static::$_total_spent_time = 0;
	}
}