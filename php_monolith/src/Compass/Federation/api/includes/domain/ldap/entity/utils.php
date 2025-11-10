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
	 * формируем user_search_filter на основе
	 *
	 * @param string $user_unique_attribute
	 * @param string $user_unique_attribute_value
	 *
	 * @return string
	 */
	public static function formatUserFilterByUniqueAttribute(string $user_unique_attribute, string $user_unique_attribute_value):string {

		$user_unique_attribute_value = match (mb_strtolower($user_unique_attribute)) {
			"objectguid" => self::_uuidStringToBin($user_unique_attribute_value),
			"objectsid" => self::_sidStringToBin($user_unique_attribute_value),
			default => $user_unique_attribute_value,
		};

		return sprintf("(&(|(objectCategory=person)(objectClass=person))(%s=%s))", $user_unique_attribute, $user_unique_attribute_value);
	}

	/**
	 * формируем user_search_filter на основе
	 *
	 * @return string
	 */
	public static function formatUserFilter(string $filter, string $user_unique_attribute_value):string {

		return format($filter, ["0" => $user_unique_attribute_value]);
	}

	/**
	 * парсим значения атрибутов учетной записи
	 *
	 * @param array  $entry
	 * @param string $user_login_attribute
	 * @param string $user_unique_attribute
	 *
	 * @return array
	 * @throws ParseFatalException
	 */
	public static function parseEntryAttributes(array $entry, string $user_login_attribute, string $user_unique_attribute):array {

		return [
			self::getLoginAttributeValue($entry, $user_login_attribute),
			self::getUniqueAttributeValue($entry, $user_unique_attribute),
			self::getDnAttribute($entry),
		];
	}

	/**
	 * получаем знаение атрибута по его названию
	 *
	 * @return string
	 */
	public static function getAttribute(array $entry, string $attribute_name):string {

		return $entry[$attribute_name] ?? "";
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
	 * получаем значение логин атрибута учетной записи
	 *
	 * @param array  $entry
	 * @param string $login_attribute
	 *
	 * @return string
	 * @throws ParseFatalException
	 */
	public static function getLoginAttributeValue(array $entry, string $login_attribute):string {

		$login_attribute = mb_strtolower($login_attribute);
		if (!isset($entry[$login_attribute])) {
			throw new ParseFatalException("incorrect login attribute");
		}

		return $entry[$login_attribute];
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
	 * Подготавливаем учетную запись LDAP, оставляя только атрибуты, конвертируая бинарные значения в строковые
	 *
	 * @param array $entry
	 *
	 * @return array
	 */
	public static function prepareEntry(array $entry):array {

		$output = [];
		for ($i = 0; $i < $entry["count"]; $i++) {

			$attribute_name        = $entry[$i];
			$output_attribute_name = mb_strtolower($attribute_name);
			$attribute_value       = $entry[$attribute_name][0];

			$attribute_value = match ($output_attribute_name) {
				"objectsid" => self::sidBinToString($attribute_value),
				"objectguid" => self::uuidBinToString($attribute_value),
				default => $attribute_value,
			};

			$output[$output_attribute_name] = $attribute_value;
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

	/**
	 * Конвертировать полученный uuid/guid в бинарном виде из LDAP в строковый
	 *
	 * @doc https://datatracker.ietf.org/doc/html/rfc4122
	 * @param string $uuid_bin
	 *
	 * @return string|false
	 */
	public static function uuidBinToString(string $uuid_bin):string|false {

		// дали уже готовый uuid/guid, его же и возвращаем
		if (isUuidValid($uuid_bin)) {
			return $uuid_bin;
		}

		// некорректная длина для uuid
		if (strlen($uuid_bin) != 16) {
			return false;
		}

		// Разбиваем бинарные данные на части (учитывая порядок байт)
		$unpacked = unpack(
			'Vtime_low/vtime_mid/vtime_hi_and_version/Cclock_seq_hi/Cclock_seq_low/H12node',
			$uuid_bin
		);

		// Форматируем в строку RFC 4122
		return sprintf(
			'%08x-%04x-%04x-%02x%02x-%012s',
			$unpacked['time_low'],
			$unpacked['time_mid'],
			$unpacked['time_hi_and_version'],
			$unpacked['clock_seq_hi'],
			$unpacked['clock_seq_low'],
			$unpacked['node']
		);
	}

	/**
	 * Конвертировать полученный sid в бинарном виде из LDAP в строковый
	 *
	 * @doc https://learn.microsoft.com/en-us/openspecs/windows_protocols/ms-dtyp/78eb9013-1c3a-4970-ad1f-2b1dad588a25
	 * @param string $sid_bin
	 *
	 * @return string|false
	 */
	public static function sidBinToString(string $sid_bin):string|false {

		// дали в конвертацию уже готовую строку, ее же и отдаем
		if (str_starts_with($sid_bin, "S-")) {
			return $sid_bin;
		}

		$revision = ord($sid_bin[0]);

		// поддерживается только 1-я ревизия
		if ($revision !== 1) {
			return false;
		}

		$sub_auth_count = ord($sid_bin[1]);

		$identifier_authority = 0;
		for ($i = 2; $i <= 7; $i++) {
			$identifier_authority = ($identifier_authority << 8) | ord($sid_bin[$i]);
		}

		$offset        = 8;
		$sub_auth_list = [];
		for ($i = 0; $i < $sub_auth_count; $i++) {

			$sub_auth_list[] = unpack("V", substr($sid_bin, $offset, 4))[1];
			$offset          += 4;
		}

		$sub_auth_string = "";
		foreach ($sub_auth_list as $sub_auth) {
			$sub_auth_string .= "-" . $sub_auth;
		}

		return "S-$revision-$identifier_authority" . $sub_auth_string;
	}

	/**
	 * Конвертировать строку uuid/guid в бинарный формат
	 * @param string $uuid
	 *
	 * @return string|false
	 */
	protected static function _uuidStringToBin(string $uuid):string|false {

		$uuid = preg_replace("/[^a-f0-9]/", "", mb_strtolower($uuid));

		// если прислали не uuid, то завершаем
		if (strlen($uuid) != 32) {
			return false;
		}

		$time_low            = substr($uuid, 0, 8);
		$time_mid            = substr($uuid, 8, 4);
		$time_hi_and_version = substr($uuid, 12, 4);
		$clock_seq_hi        = substr($uuid, 16, 2);
		$clock_seq_low       = substr($uuid, 18, 2);
		$node                = substr($uuid, 20, 12);

		return pack(
			"VvvCCH12",
			hexdec($time_low),
			hexdec($time_mid),
			hexdec($time_hi_and_version),
			hexdec($clock_seq_hi),
			hexdec($clock_seq_low),
			hexdec($node)
		);
	}

	/**
	 * Конвертировать sid в бинарный формат
	 * @param string $sid
	 *
	 * @return string|false
	 */
	protected static function _sidStringToBin(string $sid):string|false {

		// если строка не начинается с S-, значит нам дали не sid
		if (!str_starts_with($sid, "S-")) {
			return false;
		}

		$sid_parts = explode("-", $sid);

		// если в строке меньше 3 частей, то это невалидный sid
		if (count($sid_parts) < 3) {
			return false;
		}

		$revision             = (int) $sid_parts[1];
		$identifier_authority = (int) $sid_parts[2];
		$sub_auth_list        = array_slice($sid_parts, 3);

		// поддерживается только первая ревизия
		if ($revision !== 1) {
			return false;
		}

		$sid_bin = chr($revision);
		$sid_bin .= chr(count($sub_auth_list));

		for ($i = 5; $i >= 0; $i--) {
			$byte    = ($identifier_authority >> (8 * $i)) & 0xFF;
			$sid_bin .= chr($byte);
		}

		foreach ($sub_auth_list as $sub_auth) {
			$sub_auth = (int) $sub_auth;
			$sid_bin  .= pack("V", $sub_auth);
		}

		return $sid_bin;
	}
}