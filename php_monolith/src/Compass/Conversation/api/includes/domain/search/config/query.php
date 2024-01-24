<?php

namespace Compass\Conversation;

/**
 * Класс, описывающий настройки параметры запросов в индекса.
 */
class Domain_Search_Config_Query {

	/**
	 * Сколько локаций можно найти в рамках одного запроса.
	 *
	 * Работает на весь запрос, не учитывая смещение.
	 * По сути ограничивает верхнее значение для смещения при поиске.
	 */
	public static function getLocationPerSearchMaxMatches():int {

		$config = static::_load();
		return $config["max_location_matches"] ?? 1000;
	}

	/**
	 * Сколько совпадений можно найти в рамках одного запроса.
	 *
	 * аботает на весь запрос, не учитывая смещение.
	 * По сути ограничивает верхнее значение для смещения при поиске.
	 */
	public static function getHitPerSearchMaxMatches():int {

		$config = static::_load();
		return $config["max_hit_matches"] ?? 1000;
	}

	/**
	 * Загружает конфиг доступа к поиску.
	 */
	protected static function _load():array {

		$config = getConfig("SEARCH");
		return $config["query"] ?? [];
	}
}