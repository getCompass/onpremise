<?php

namespace BaseFrame\System;

use BaseFrame\Exception\Domain\LocaleNotFound;
use BaseFrame\Exception\Domain\LocaleTextNotFound;

/**
 * Класс для локализации текстов
 */
class Locale {

	public const LOCALE_ENGLISH = "en-US"; // английская локаль
	public const LOCALE_RUSSIAN = "ru-RU"; // русская локаль
	public const LOCALE_ESPANOL = "es-ES"; // испанская локаль
	public const LOCALE_ITALIAN = "it-IT"; // итальянская локаль
	public const LOCALE_DEUTSCH = "de-DE"; // немецкая локаль
	public const LOCALE_FRENCH  = "fr-FR"; // французская локаль

	// разрешенные локали на сервере
	protected const _ALLOWED_LOCALE_LIST = [
		self::LOCALE_RUSSIAN,
		self::LOCALE_ENGLISH,
		self::LOCALE_ESPANOL,
		self::LOCALE_FRENCH,
		self::LOCALE_ITALIAN,
		self::LOCALE_DEUTSCH,
	];

	/**
	 * Получить локаль пользователя
	 *
	 * @return string
	 */
	public static function getLocale():string {

		// если локаль не установлена или неверная - ставим русский язык
		if (!isset($_SERVER["HTTP_X_COMPASS_LOCALE"])) {
			$_SERVER["HTTP_X_COMPASS_LOCALE"] = self::LOCALE_RUSSIAN;
		}

		if (!in_array($_SERVER["HTTP_X_COMPASS_LOCALE"], self::_ALLOWED_LOCALE_LIST)) {
			$_SERVER["HTTP_X_COMPASS_LOCALE"] = self::LOCALE_ENGLISH;
		}

		return $_SERVER["HTTP_X_COMPASS_LOCALE"];
	}

	/**
	 * Получить язык пользователя
	 *
	 * @param string|false $locale
	 *
	 * @return string
	 */
	public static function getLang(string|false $locale = false):string {

		// если переданной локали нет в списке доступных - возвращаем английский
		if ($locale !== false && !in_array($locale, self::_ALLOWED_LOCALE_LIST)) {
			$locale = self::LOCALE_ENGLISH;
		}

		// получаем локаль, если ничего не передали
		if ($locale === false) {
			$locale = self::getLocale();
		}

		// выдергиваем из локали язык пользователя (он указывается до дефиса)
		return strtok($locale, "-");
	}

	/**
	 * Получаем строку текста из переданного конфига с локализацией
	 *
	 * @param array  $config_text
	 * @param string $group
	 * @param string $key
	 * @param string $locale
	 * @param array  $values
	 *
	 * @return string
	 * @throws LocaleTextNotFound
	 */
	public static function getText(array $config_text, string $group, string $key, string $locale = "", array $values = []):string {

		if ($locale === "") {
			$locale = self::LOCALE_RUSSIAN;
		}

		if (!isset($config_text[$locale], $config_text[$locale][$group], $config_text[$locale][$group][$key])) {
			throw new LocaleTextNotFound("cant found locale text");
		}

		$text = $config_text[$locale][$group][$key];

		// заменяем ключи в тексте на их значения
		foreach ($values as $value_key => $value) {
			$text = str_replace("{" . $value_key . "}", $value, $text);
		}
		return $text;
	}

	/**
	 * Проверить, что локаль доступна
	 *
	 * @param string|false $locale
	 *
	 * @return void
	 * @throws LocaleNotFound
	 */
	public static function assertAllowedLocale(string|false $locale = false):void {

		if ($locale === false) {
			$locale = self::getLocale();
		}

		if (in_array($locale, self::_ALLOWED_LOCALE_LIST)) {
			return;
		}

		throw new LocaleNotFound("locale not found");
	}
}