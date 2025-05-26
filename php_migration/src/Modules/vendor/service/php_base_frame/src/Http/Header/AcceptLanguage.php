<?php

namespace BaseFrame\Http\Header;

/**
 * Обработчик для хедера Accept-Language
 */
class AcceptLanguage extends Header {

	protected const _HEADER_KEY = "ACCEPT_LANGUAGE"; // ключ хедера

	/**
	 * Получить список предпочитаемых пользователем языков
	 *
	 * @return array
	 */
	public function getPreferredLocales():array {

		// делим хедер по языкам
		$exploded_header_value = explode(",", self::getValue());

		// определяем callback функцию, которая формирует список языков из хедера
		$callback_func = function(array $result, string $item) {

			// делим каждую локаль на сам язык и его вес у клиента
			$locale_item_arr = explode(";q=", $item);

			// делаем array_merge с единицей и возвращаем два первых элемента из массив
			// нужно, чтобы мы валидно обработали языки, которым не дали веса (хедер типа fr, it;q=0.9). Тогда у fr будет вес 1, а у it 0.9
			[$locale, $quality] = array_merge($locale_item_arr, [1]);

			// обрезаем пробелы в локали
			$locale = trim($locale);

			// формируем список предпочитаемых локалей
			$result[$locale] = (float) $quality;
			return $result;
		};

		// формируем список предпочитаемых локалей
		$preferred_locales = array_reduce($exploded_header_value, $callback_func, []);

		// сортируем от большего к меньшему
		arsort($preferred_locales);

		return $preferred_locales;
	}
}