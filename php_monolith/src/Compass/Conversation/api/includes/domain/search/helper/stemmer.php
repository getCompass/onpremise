<?php

namespace Compass\Conversation;

use BaseFrame\System\Locale;
use Wamania\Snowball\NotFoundException;
use Wamania\Snowball\Stemmer\Stemmer;
use Wamania\Snowball\StemmerFactory;

/**
 * класс для работы со стеммером
 */
class Domain_Search_Helper_Stemmer {

	protected static float $_total_spent_time = 0;

	/**
	 * singleton для инстансов stemmer каждой из локали
	 */
	protected static array $_stemmer_instance_map = [];

	/**
	 * Список поддерживаемых стеммером локалей. Расставлены по следующему принципу:
	 * 1. В первую очередь стоит русская локаль, поскольку приложение в первую очередь на русскоговорящую аудиторию
	 * 1. Все остальные локали отсортированы по популярности
	 */
	protected const _SUPPORTED_LOCATION_MAP = [
		Locale::LOCALE_RUSSIAN => "ru",
		Locale::LOCALE_ENGLISH => "en",
		Locale::LOCALE_ESPANOL => "es",
		Locale::LOCALE_FRENCH  => "fr",
		Locale::LOCALE_ITALIAN => "it",
		Locale::LOCALE_DEUTSCH => "de",
	];

	/**
	 * Минимальная длина слова для локалей
	 */
	protected const _LOCATION_WORD_MIN_LEN = [
		Locale::LOCALE_RUSSIAN => 3,
		Locale::LOCALE_ENGLISH => 3,
		Locale::LOCALE_ESPANOL => 3,
		Locale::LOCALE_FRENCH  => 3,
		Locale::LOCALE_ITALIAN => 3,
		Locale::LOCALE_DEUTSCH => 3,
	];

	/**
	 * Получаем инстанс стеммера
	 *
	 * @return Stemmer
	 * @throws NotFoundException
	 */
	public static function instance(string $language):Stemmer {

		if (!isset(static::$_stemmer_instance_map[$language])) {
			static::$_stemmer_instance_map[$language] = StemmerFactory::create($language);
		}

		return static::$_stemmer_instance_map[$language];
	}

	/**
	 * Получаем основу слова.
	 * @throws \Exception
	 */
	public static function stemWord(string $word, string $locale):string {

		// код языка
		$language_code = self::_getLocaleToLanguageCode($locale);

		// пытаемся получить стеммер для нужного языка
		try {
			$stemmer = self::instance($language_code);
		} catch (NotFoundException) {

			// в таком случае просто вернем слово в изначальном виде
			return $word;
		}

		return $stemmer->stem($word);
	}

	/**
	 * Получаем основу для каждого слова в тексте
	 *
	 * @param string $text
	 * @param array  $priority_locale_list Список приоритетных локалей, через которые нужно пропустить текст в первую очередь.
	 *                                     Задумка в том, чтобы каждое слово текста в первую очередь прогонялось через приоритетные локали:
	 *                                     – локаль пользователя
	 *                                     – en-EN (международный язык)
	 *                                     после приоритетных локалей гоним по всем остальным из @const self::_SUPPORTED_LOCATION_MAP
	 *                                     исключая из нее все локали из $priority_locale_list
	 *
	 * @return string
	 * @throws \Exception
	 */
	public static function stemText(string $text, array $priority_locale_list):string {

		$start_at = microtime(true);

		// переводим слово в нижний регистр
		$text = mb_strtolower($text);

		// заменяем часть букв для корректной обработки запросов
		// например, в русской локализации слова с 'ё' работает отлично от 'е'
		$text = Domain_Search_Helper_Query_Sanitizer::replaceSomeLetters($text);

		// разбиваем текст по словам
		$word_list = splitTextIntoWords($text);

		// финальный приоритезированный список языков для стеммера
		$locale_list = self::_getFinalyLocaleList($priority_locale_list);

		// сюда сложим ответ
		$output_word_list = [];

		// пробегаемся по каждому слову
		foreach ($word_list as $word) {

			// переводим слово в нижний регистр
			$word = mb_strtolower($word);

			// в эту переменную запишем финальную основу, которую получим
			// по умолчанию здесь записано исходное слово
			$stemming_word = $word;

			// проходимся по каждой локали
			foreach ($locale_list as $locale) {

				// получаем основу слова
				$temp = self::stemWord($word, $locale);

				// если длина слова меньше минимальной длины для локали, то берём оригинальное слово
				if (mb_strlen($temp) < self::_LOCATION_WORD_MIN_LEN[$locale]) {
					break;
				}

				// если основа отличается от исходного слова, то помещаем это слово в ответ и дальше не бежим по локалям
				if ($temp !== $word) {

					$stemming_word = $temp;
					break;
				}
			}

			// помещаем получившуюся основу в ответ
			$output_word_list[] = $stemming_word;
		}

		// возвращаем ответ из полученных слов
		static::$_total_spent_time += microtime(true) - $start_at;
		return implode(" ", $output_word_list);
	}

	/**
	 * Получаем финальный список языков, отсортированных по приоритету, для прогона через стеммер
	 *
	 * @return array
	 */
	protected static function _getFinalyLocaleList(array $priority_locale_list):array {

		// собираем все в один уникальный массив
		return array_unique(array_merge($priority_locale_list, array_keys(self::_SUPPORTED_LOCATION_MAP)));
	}

	/**
	 * Получаем код языка локали
	 */
	protected static function _getLocaleToLanguageCode(string $locale):string {

		// если пришла левачная локаль
		if (!isset(self::_SUPPORTED_LOCATION_MAP[$locale])) {
			throw new \BaseFrame\Exception\Domain\ParseFatalException("unexpected locale");
		}

		return self::_SUPPORTED_LOCATION_MAP[$locale];
	}

	/**
	 * Записывает метрики времени исполнения.
	 */
	public static function writeMetrics():void {

		\BaseFrame\Monitor\Core::metric("stemmer_work_time_ms", (int) (static::$_total_spent_time * 1000))->seal();
		static::$_total_spent_time = 0;
	}
}