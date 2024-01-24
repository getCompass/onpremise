<?php

namespace Compass\Pivot;

/**
 * Класс для работы с экстра-данными регистрации пользователя.
 */
class Domain_User_Entity_RegistrationExtra {

	// версия упаковщика
	protected const _EXTRA_VERSION = 2;

	// схема extra по версиям
	protected const _EXTRA_SCHEMA = [
		1 => [
			"ip_address"                     => "",
			"autonomous_system_code"         => 0,
			"autonomous_system_country_code" => "",
			"autonomous_system_country_name" => "",
		],
		2 => [
			"ip_address"                     => "",
			"autonomous_system_code"         => 0,
			"autonomous_system_country_code" => "",
			"autonomous_system_country_name" => "",
			"autonomous_system_name"         => "",
		],
	];

	/**
	 * Создать новую структуру для extra.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	public static function initExtra():array {

		return [
			"version" => static::_EXTRA_VERSION,
			"extra"   => static::_EXTRA_SCHEMA[static::_EXTRA_VERSION],
		];
	}

	/**
	 * Устанавливает все поля регистрации.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	public static function set(array $extra, string $ip_address, int $autonomous_system_code, string $autonomous_system_country_code, string $autonomous_system_name):array {

		$extra = static::setAutonomousSystemName($extra, $autonomous_system_name);
		$extra = static::setAutonomousSystemCode($extra, $autonomous_system_code);
		$extra = static::setAutonomousSystemCountryCode($extra, $autonomous_system_country_code);

		return static::setIpAddress($extra, $ip_address);
	}

	/**
	 * Возвращает все поля регистрации.
	 */
	public static function get(array $extra):array {

		$extra = self::_getExtra($extra);

		return [
			$extra["extra"]["ip_address"],
			$extra["extra"]["autonomous_system_name"],
			$extra["extra"]["autonomous_system_code"],
			$extra["extra"]["autonomous_system_country_code"],
		];
	}

	/**
	 * Устанавливает ip адрес, с которого была совершена регистрация.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	public static function setIpAddress(array $extra, string $ip_address):array {

		$extra = self::_getExtra($extra);

		// обновляем данные премиума
		$extra["extra"]["ip_address"] = $ip_address;
		return $extra;
	}

	/**
	 * Устанавливает имя автономной системы, с которого была совершена регистрация.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	public static function setAutonomousSystemName(array $extra, string $autonomous_system_name):array {

		$extra = self::_getExtra($extra);

		// обновляем данные премиума
		$extra["extra"]["autonomous_system_name"] = $autonomous_system_name;
		return $extra;
	}

	/**
	 * Устанавливает код автономной системы, с которого была совершена регистрация.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	public static function setAutonomousSystemCode(array $extra, int $autonomous_system_code):array {

		$extra = self::_getExtra($extra);

		// обновляем данные премиума
		$extra["extra"]["autonomous_system_code"] = $autonomous_system_code;
		return $extra;
	}

	/**
	 * Устанавливает страну, из которой была совершена регистрация.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	public static function setAutonomousSystemCountryCode(array $extra, string $autonomous_system_country_code):array {

		$extra = self::_getExtra($extra);

		// обновляем данные премиума
		$extra["extra"]["autonomous_system_country_code"] = $autonomous_system_country_code;
		return $extra;
	}

	# region protected

	/**
	 * Получить актуальную структуру для extra
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	protected static function _getExtra(array $extra):array {

		// если версия не совпадает - дополняем её до текущей
		if ((int) $extra["version"] !== static::_EXTRA_VERSION) {

			$extra["extra"]   = array_merge(static::_EXTRA_SCHEMA[static::_EXTRA_VERSION], $extra["extra"]);
			$extra["version"] = static::_EXTRA_VERSION;
		}

		return $extra;
	}

	# endregion protected
}
