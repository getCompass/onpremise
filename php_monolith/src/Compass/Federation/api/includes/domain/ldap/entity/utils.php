<?php

namespace Compass\Federation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * вспомогательный класс для работы с LDAP
 * @package Compass\Federation
 */
class Domain_Ldap_Entity_Utils {

	/**
	 * формируем dn (Distinguished Name) пользователя – этот параметр идентифицирует учетную запись,
	 * под которой клиент будет выполнять запросы к серверу LDAP
	 *
	 * @return string
	 */
	public static function makeUserDn(string $base, string $user_unique_attribute, string $user_unique_attribute_value):string {

		return sprintf("%s=%s,%s", $user_unique_attribute, $user_unique_attribute_value, $base);
	}

	/**
	 * форматируем user_search_filter, подставляя значение для поиска по фильтру
	 *
	 * @return string
	 */
	public static function formatUserFilter(string $user_unique_attribute, string $user_unique_attribute_value):string {

		return sprintf("(&(|(objectCategory=person)(objectClass=person))(%s=%s))", $user_unique_attribute, $user_unique_attribute_value);
	}

	/**
	 * парсим значения атрибутов учетной записи
	 *
	 * @return array
	 */
	public static function parseEntryAttributes(array $entry, string $user_unique_attribute):array {

		return [
			self::getUniqueAttributeValue($entry, $user_unique_attribute),
			self::getDnAttribute($entry),
		];
	}

	/**
	 * получаем display name учетной записи
	 *
	 * @return string
	 */
	public static function getDisplayNameAttribute(array $entry):string {

		return $entry["displayname"] ?? "";
	}

	/**
	 * получаем значение уникального атрибута учетной записи
	 *
	 * @param array  $entry
	 * @param string $unique_attribute
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	public static function getUniqueAttributeValue(array $entry, string $unique_attribute):string {

		$unique_attribute = mb_strtolower($unique_attribute);
		if (!isset($entry[$unique_attribute])) {
			throw new ParseFatalException("incorrect unique attribute");
		}

		return $entry[$unique_attribute];
	}

	/**
	 * получаем dn учетной записи
	 *
	 * @return string
	 */
	public static function getDnAttribute(array $entry):string {

		return $entry["dn"] ?? "";
	}

	/**
	 * подготавливаем учетную запись LDAP, оставляя только атрибуты
	 *
	 * @return array
	 */
	public static function prepareEntry(array $entry):array {

		$output = [];
		for ($i = 0; $i < $entry["count"]; $i++) {

			$attribute_name                         = $entry[$i];
			$output[mb_strtolower($attribute_name)] = $entry[$attribute_name][0];
		}

		return $output;
	}

	/**
	 * конвертируем интервал из конфига в секунды
	 *
	 * @return int
	 */
	public static function convertIntervalToSec(string $interval):int {

		// извлекаем числовое значение и единицу измерения
		preg_match("/(\d+)([smh])/", $interval, $matches);

		// если строка не соответствует ожидаемому формату, то возвращаем 0
		if (count($matches) != 3) {
			return 0;
		}

		$value = (int) $matches[1];
		$unit  = $matches[2];

		// конвертируем в секунды в зависимости от единицы измерения
		return match ($unit) {
			"s" => $value,
			"m" => $value * 60,
			"h" => $value * 3600,
		};
	}
}