<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Класс для уровневых блокировок по IP
 */
class Type_Antispam_Leveled_Ip extends Type_Antispam_Leveled_Main {

	public const AUTH = [
		"key"   => "AUTH",
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
				"limit"  => -1,
				"expire" => HOUR1,
			],
		],
	];

	/** лимиты для онпремайза */
	public const ONPREMISE_AUTH = [
		"key"   => "AUTH",
		"level" => [
			self::LIGHT_LEVEL   => [
				"limit"  => 4,
				"expire" => HOUR1,
			],
			self::MEDIUM_LEVEL  => [
				"limit"  => 2,
				"expire" => HOUR1,
			],
			self::WARNING_LEVEL => [
				"limit"  => -1,
				"expire" => HOUR1,
			],
		],
	];

	/**
	 * получаем лимиты в зависимости от сервера
	 */
	public static function getLimitsByServer():array {

		if (ServerProvider::isOnPremise()) {
			return self::ONPREMISE_AUTH;
		}

		return self::AUTH;
	}
}