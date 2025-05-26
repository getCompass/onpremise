<?php

namespace BaseFrame\Struct\Country;

use BaseFrame\Exception\Domain\LocaleTextNotFound;
use BaseFrame\System\Locale;

/**
 * Класс-структура для элемента конфига стран
 */
class ConfigItem {

	/**
	 * Конструктор
	 *
	 * @param string $country_code
	 * @param string $name
	 * @param string $flag_emoji_short_name
	 * @param array  $phone_prefix_list
	 */
	protected function __construct(
		public string $country_code,
		public string $name,
		public string $flag_emoji_short_name,
		public array  $phone_prefix_list,
	) {
	}

	/**
	 * Сформировать объект из массива
	 *
	 * @param string $country_code
	 * @param array  $conf_item
	 *
	 * @return static
	 */
	public static function fromArray(string $country_code, array $conf_item):self {

		return new self(
			$country_code,
			$conf_item["name"],
			$conf_item["flag_emoji_short_name"],
			$conf_item["phone_prefix_list"],
		);
	}

	/**
	 * Вернуть локализованное название
	 *
	 * @param string $locale
	 *
	 * @return string
	 */
	public function getLocalizedName(string $locale = Locale::LOCALE_RUSSIAN):string {

		$locale_config = getSystemConfig("LOCALE_SYSTEM_TEXT");

		try {
			return Locale::getText($locale_config, "country", $this->country_code, $locale);
		} catch (LocaleTextNotFound) {
			return $this->name;
		}
	}
}