<?php

namespace Compass\Pivot;

/**
 * Класс для уровневых блокировок по пользовтелю
 */
class Type_Antispam_Leveled_User extends Type_Antispam_Leveled_Main {

	public const TWO_FA = [
		"key"   => "TWO_FA",
		"level" => [
			self::LIGHT_LEVEL   => [
				"limit"  => 2,
				"expire" => HOUR1,
			],
			self::MEDIUM_LEVEL  => [
				"limit"  => 2,
				"expire" => HOUR1,
			],
			self::WARNING_LEVEL => [
				"limit"  => 1,
				"expire" => HOUR1,
			],
		],
	];
}
