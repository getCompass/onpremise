<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс для работы с локализованными текстами
 */
class Type_Locale_Text {

	/**
	 * Получаем текст из конфига с локализациями
	 *
	 * @throws \parseException
	 */
	public static function getText(string $group, string $key, array $values = [], string $locale = ""):string {

		if ($locale === "") {
			$locale = "ru";
		}
		$lang_texts = getConfig("LANG_TEXTS");

		if (!isset($lang_texts[$locale]) || !isset($lang_texts[$locale][$group]) || !isset($lang_texts[$locale][$group][$key])) {
			throw new ParseFatalException("not found language");
		}

		$text = $lang_texts[$locale][$group][$key];

		// заменяем ключи в тексте на их значения
		foreach ($values as $value_key => $value) {
			$text = str_replace("{" . $value_key . "}", $value, $text);
		}
		return $text;
	}
}