<?php

namespace Compass\Pivot;

/**
 * Класс для уровневых блокировок по phone_number_hash
 */
class Type_Antispam_Leveled_Phone extends Type_Antispam_Leveled_Main {

	/**
	 * Блокировка аутентификации, которая декрементится после успеха
	 */
	public const DYNAMIC_AUTH_BLOCK = [
		"key"   => "DYNAMIC_AUTH_BLOCK",
		"level" => [
			self::LIGHT_LEVEL   => [
				"limit"  => 5,
				"expire" => HOUR1,
			],
			self::MEDIUM_LEVEL  => [
				"limit"  => 3,
				"expire" => HOUR1,
			],
			self::WARNING_LEVEL => [
				"limit"  => 3,
				"expire" => HOUR1,
			],
		],
	];

	/**
	 * Блокировка аутентификации, которая НЕ декрементится после успеха
	 */
	public const STATIC_AUTH_BLOCK = [
		"key"   => "STATIC_AUTH_BLOCK",
		"level" => [
			self::LIGHT_LEVEL   => [
				"limit"  => 10,
				"expire" => HOUR1,
			],
			self::MEDIUM_LEVEL  => [
				"limit"  => 5,
				"expire" => HOUR1,
			],
			self::WARNING_LEVEL => [
				"limit"  => 5,
				"expire" => HOUR1,
			],
		],
	];
}