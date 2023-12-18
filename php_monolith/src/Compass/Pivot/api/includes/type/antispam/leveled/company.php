<?php

namespace Compass\Pivot;

/**
 * Класс для уровневых блокировок по company_id
 */
class Type_Antispam_Leveled_Company extends Type_Antispam_Leveled_Main {

	/** @var array блок на одновременно активные приглашения */
	public const SEND_ACTIVE_INVITE = [
		"key"   => "SEND_ACTIVE_INVITE",
		"level" => [
			self::LIGHT_LEVEL   => [
				"limit"  => 50,
				"expire" => DAY1,
			],
			self::MEDIUM_LEVEL  => [
				"limit"  => 50,
				"expire" => DAY1,
			],
			self::WARNING_LEVEL => [
				"limit"  => 50,
				"expire" => DAY1,
			],
		],
	];

	/** @var array блок кол-во приглашений в день */
	public const SEND_DAILY_INVITE = [
		"key"   => "SEND_DAILY_INVITE",
		"level" => [
			self::LIGHT_LEVEL   => [
				"limit"  => 300,
				"expire" => DAY1,
			],
			self::MEDIUM_LEVEL  => [
				"limit"  => 300,
				"expire" => DAY1,
			],
			self::WARNING_LEVEL => [
				"limit"  => 300,
				"expire" => DAY1,
			],
		],
	];

	/** @var array оставил пока, эи лимиты согласованы, чтобы не потерять */
	public const SEND_INVITE_WHITE_LIST = [
		"key"   => "SEND_INVITE",
		"level" => [
			self::LIGHT_LEVEL   => [
				"limit"  => 300,
				"expire" => DAY1,
			],
			self::MEDIUM_LEVEL  => [
				"limit"  => 200,
				"expire" => DAY1,
			],
			self::WARNING_LEVEL => [
				"limit"  => 30,
				"expire" => DAY1,
			],
		],
	];

	public const TWO_FA = [
		"key"   => "TWO_FA",
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
				"limit"  => 0,
				"expire" => HOUR1,
			],
		],
	];
}