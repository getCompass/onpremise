<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Базовый класс для уровневых блокировок
 */
class Type_Antispam_Leveled_Main {

	// ключ уровня блокировки
	public const CONFIG_BLOCK_LEVEL_KEY = "ANTISPAM_BLOCK_LEVEL";

	public const LIGHT_LEVEL   = 1;
	public const MEDIUM_LEVEL  = 2;
	public const WARNING_LEVEL = 3;

	// список форматов под каждый уровень блокировок
	public const TYPE_LVL_FORMAT = [
		self::LIGHT_LEVEL   => "light",
		self::MEDIUM_LEVEL  => "medium",
		self::WARNING_LEVEL => "warning",
	];

	/**
	 * установить уровень блокировки
	 *
	 */
	public static function setBlockLevel(int $block_level):void {

		$value = ["value" => $block_level];
		Type_System_Config::init()->set(self::CONFIG_BLOCK_LEVEL_KEY, $value);
	}

	/**
	 * получить уровень блокировки
	 *
	 */
	public static function getBlockLevel():int {

		$block_level_conf = Type_System_Config::init()->getConf(self::CONFIG_BLOCK_LEVEL_KEY);

		// если в базе не установлен уровень блокировки, то по умолчанию лайт
		return $block_level_conf["value"] ?? self::LIGHT_LEVEL;
	}

	/**
	 * проверяем, средний ли уровень блокировок
	 *
	 */
	public static function isMediumLevel():bool {

		return self::getBlockLevel() === self::MEDIUM_LEVEL;
	}

	/**
	 * проверяем, опасный ли уровень блокировки
	 *
	 */
	public static function isWarningLevel():bool {

		return self::getBlockLevel() === self::WARNING_LEVEL;
	}

	public static function getBlockRule(array $level_rules):array {

		$level = self::getBlockLevel();

		return [
			"key"    => $level_rules["key"],
			"limit"  => $level_rules["level"][$level]["limit"],
			"expire" => $level_rules["level"][$level]["expire"],
		];
	}

	/**
	 * получаем отформатированный уровень блокировки по int-типу
	 *
	 * @throws \returnException
	 */
	public static function getFormatBlockLevelByType(int $level_type):string {

		if (!isset(self::TYPE_LVL_FORMAT[$level_type])) {

			throw new ReturnFatalException("Incorrect block level: {$level_type}");
		}

		// из int-тип получаем уровень в string-тип
		return self::TYPE_LVL_FORMAT[$level_type];
	}

	/**
	 * получаем int-тип уровня блокировки по форматированному string-типу
	 *
	 * @throws \returnException
	 */
	public static function getBlockLevelTypeByFormatType(string $level_type):int {

		$block_level_list = array_flip(self::TYPE_LVL_FORMAT);

		if (!isset($block_level_list[$level_type])) {

			throw new ReturnFatalException("Incorrect block level: {$level_type}");
		}

		return $block_level_list[$level_type];
	}
}