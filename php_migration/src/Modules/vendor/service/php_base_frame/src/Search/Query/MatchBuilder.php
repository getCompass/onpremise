<?php declare(strict_types=1);

namespace BaseFrame\Search\Query;

use BaseFrame\Search\Exception\QueryException;

/**
 * Класс для работы с поисковыми запросами.
 */
class MatchBuilder {

	/** @var int ограничение на длину строки для поиска */
	protected const _SEARCH_QUERY_STRING_LENGTH = 80;

	/** @var int ограничение на минимальную длину слова для добавления поискового суффикса */
	protected const _WORD_MIN_LENGTH_FOR_WILDCARD_SUFFIX = 3;

	/** @var array символы для экранирования, в мантикоре экранирование спецсимволов делается двумя слэшами */
	protected const _SYMBOL_LIST_TO_ESCAPE = [
		"\\" => "\\\\\\\\",
		"!"  => "\\!",
		"\"" => "\\\"",
		"$"  => "\\$",
		"("  => "\\(",
		")"  => "\\)",
		"-"  => "\\-",
		"/"  => "\\/",
		"<"  => "\\<",
		"@"  => "\\@",
		"^"  => "\\^",
		"|"  => "\\|",
		"~"  => "\\~",
	];

	/** @var int только по целым словам */
	public const SEARCH_MODE_PLANE = 1;
	/** @var int по подстрокам с префиксами */
	public const SEARCH_MODE_WILDCARD_PREFIX = 2;
	/** @var int по подстрокам с инфиксами */
	public const SEARCH_MODE_WILDCARD = 3;
	/** @var int по подстрокам с инфиксами и приоритетам для префиксов */
	public const SEARCH_MODE_WILDCARD_PREFER_PREFIX = 4;

	/**
	 * Выполняет подготовку запроса для поиска.
	 *
	 * @param string $search_query
	 * @param int    $mode
	 *
	 * @return string
	 * @throws QueryException
	 */
	public static function prepareQuery(string $search_query, int $mode = self::SEARCH_MODE_WILDCARD):string {

		// чистим всю сроку от мусора
		$search_query         = self::_clearQuery($search_query);
		$escaped_search_query = self::_escapeQuery($search_query);

		// разбиваем строку на слова и убираем пробелы
		$word_list         = array_filter(explode(" ", $search_query));
		$escaped_word_list = array_filter(explode(" ", $escaped_search_query));

		$prepared_word_list = match ($mode) {
			self::SEARCH_MODE_PLANE                  => self::_preparePlainQuery($escaped_word_list),
			self::SEARCH_MODE_WILDCARD_PREFIX        => self::_prepareWildcardPrefixQuery($escaped_word_list, $word_list),
			self::SEARCH_MODE_WILDCARD               => self::_prepareWildcardQuery($escaped_word_list, $word_list),
			self::SEARCH_MODE_WILDCARD_PREFER_PREFIX => self::_prepareWildcardPreferPrefixQuery($escaped_word_list, $word_list),
			default                                  => throw new QueryException("passed incorrect search query mode"),
		};

		return self::_bakeQuery($prepared_word_list);
	}

	/**
	 * Формирует строку для поиска.
	 * Простой поиск по полному вхождению слов.
	 *
	 * @param array $word_list
	 *
	 * @return array
	 */
	protected static function _preparePlainQuery(array $word_list):array {

		// убирать односимвольные слова, он все равно не ищутся?
		return $word_list;
	}

	/**
	 * Формирует строку для поиска.
	 * Все слова будут конвертированы в префиксы.
	 *
	 * @param array $escaped_word_list
	 * @param array $word_list
	 *
	 * @return array
	 */
	protected static function _prepareWildcardPrefixQuery(array $escaped_word_list, array $word_list):array {

		$output = [];

		foreach ($escaped_word_list as $index => $escaped_word) {
			$output[] = self::_addWildcardSuffix($escaped_word, $word_list[$index]);
		}

		// формируем итоговый запрос
		return $output;
	}

	/**
	 * Формирует строку для поиска.
	 * Все слова будут конвертированы в инфиксы.
	 *
	 * @param array $escaped_word_list
	 * @param array $word_list
	 *
	 * @return array
	 */
	protected static function _prepareWildcardQuery(array $escaped_word_list, array $word_list):array {

		$prepared_word_list = [];

		foreach ($escaped_word_list as $index => $escaped_word) {

			// добавляем поиск по суффиксу и префиксу
			$prepared_word = self::_addWildcardPrefix($escaped_word, $word_list[$index]);
			$prepared_word = self::_addWildcardSuffix($prepared_word, $word_list[$index]);

			$prepared_word_list[] = $prepared_word;
		}

		// формируем итоговый запрос
		return $prepared_word_list;
	}

	/**
	 * Формирует слова для запроса.
	 * Все слова будут конвертированы в инфиксы + префиксы.
	 *
	 * Задание веса идет в таком виде.
	 * query* > *query*
	 *
	 * Для искомой строки search найдет «search» и «research», но для search вес выборки будет выше.
	 *
	 * @param array $escaped_word_list
	 * @param array $word_list
	 *
	 * @return array
	 */
	protected static function _prepareWildcardPreferPrefixQuery(array $escaped_word_list, array $word_list):array {

		// готовим строку для работы с префиксами
		$prepared_word_list = [];

		foreach ($escaped_word_list as $index => $escaped_word) {

			// добавляем поиск по суффиксу и префиксу для первой части
			$prepared_word_infix = self::_addWildcardPrefix($escaped_word, $word_list[$index]);
			$prepared_word_infix = self::_addWildcardSuffix($prepared_word_infix, $word_list[$index]);

			// добавляем поиск по суффиксу для второй части и повышаем ей вес в выборке
			$prepared_word_prefix = self::_addWildcardSuffix($escaped_word, $word_list[$index]);

			if ($prepared_word_infix !== $prepared_word_prefix) {
				$prepared_word_prefix = self::_addBooster($prepared_word_prefix);
			}

			// формируем строку с or выборкой
			$prepared_word_list[] = self::_addOrCondition($prepared_word_prefix, $prepared_word_infix);
		}

		// формируем итоговый запрос
		return $prepared_word_list;
	}

	# region protected

	/**
	 * Добавляет поддержку произвольного суффикса для слова.
	 * query >> query*
	 *
	 * @param string $str
	 * @param string $src
	 *
	 * @return string
	 */
	protected static function _addWildcardSuffix(string $str, string $src):string {

		if (mb_strlen($src) < self::_WORD_MIN_LENGTH_FOR_WILDCARD_SUFFIX) {
			return $str;
		}

		return "{$str}*";
	}

	/**
	 * Добавляет поддержку произвольного префикса для слова.
	 * query >> *query
	 *
	 * @param string $str
	 * @param string $src
	 *
	 * @return string
	 */
	protected static function _addWildcardPrefix(string $str, string $src):string {

		if (mb_strlen($src) <= 1) {
			return $str;
		}

		// если это экранированный символ
		if (in_array($str, array_values(static::_SYMBOL_LIST_TO_ESCAPE))) {
			return $str;
		}

		return "*{$str}";
	}

	/**
	 * Добавляет вес для слова.
	 * query >> query^n
	 *
	 * @param string $str
	 * @param int    $val
	 *
	 * @return string
	 */
	protected static function _addBooster(string $str, int $val = 2):string {

		if (mb_strlen($str) === 0) {
			return $str;
		}

		return "{$str}^{$val}";
	}

	/**
	 * Добавляет или
	 *
	 * @param string $str_one
	 * @param string $str_two
	 *
	 * @return string
	 */
	protected static function _addOrCondition(string $str_one, string $str_two):string {

		// если строки одинаковые, то условие не нужно
		// тут же проверится, что они обе непустые
		if ($str_one === $str_two) {
			return $str_one;
		}

		// если одна из строк пустая, то возвращаем непустую
		if (mb_strlen($str_one) === 0) {
			return $str_two;
		}

		// если одна из строк пустая, то возвращаем непустую
		if (mb_strlen($str_two) === 0) {
			return $str_one;
		}

		// строки разные и непустые
		return "({$str_one}|{$str_two})";
	}

	/**
	 * Очищает строку от левых символов.
	 *
	 * @param string $query
	 *
	 * @return string
	 */
	protected static function _clearQuery(string $query):string {

		// если длина запроса больше максимального значения
		$query = substr($query, 0, self::_SEARCH_QUERY_STRING_LENGTH);

		// переводим все в нижний регистр
		$query = mb_strtolower($query);

		return trim($query);
	}

	/**
	 * Экранирует строку.
	 *
	 * @param string $query
	 *
	 * @return string
	 */
	protected static function _escapeQuery(string $query):string {

		// экранируем символы, одинарная кавычка экранируется на уровне драйвера мантикоры
		return str_replace(array_keys(static::_SYMBOL_LIST_TO_ESCAPE), array_values(static::_SYMBOL_LIST_TO_ESCAPE), $query);
	}

	/**
	 * Формирует итоговый запрос.
	 *
	 * @param array $str_list
	 *
	 * @return string
	 */
	protected static function _bakeQuery(array $str_list):string {

		// убираем пустые строки
		$str_list = array_filter($str_list);

		if (count($str_list) === 0) {
			return "";
		}

		// формируем итоговый запрос
		return "(" . implode(" ", $str_list) . ")";
	}

	# endregion protected
}
