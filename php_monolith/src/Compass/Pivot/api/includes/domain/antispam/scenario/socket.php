<?php

namespace Compass\Pivot;

/**
 * Сценарии антиспама для сокета
 */
class Domain_Antispam_Scenario_Socket {

	/**
	 * получаем текущий уровень блокировки
	 *
	 * @throws \returnException
	 */
	public static function getCurrentBlockLevel():string {

		// получаем текущий уровень блокировки
		$block_level = Type_Antispam_Leveled_Main::getBlockLevel();

		// преобразуем в string-тип и возвращаем
		return Type_Antispam_Leveled_Main::getFormatBlockLevelByType($block_level);
	}

	/**
	 * устанавливаем новый уровень блокировки
	 *
	 * @throws \returnException
	 */
	public static function setBlockLevel(string $new_block_level):void {

		// преобразуем блокировку в числовой
		$new_block_level = Type_Antispam_Leveled_Main::getBlockLevelTypeByFormatType($new_block_level);

		// устанавливаем новый уровень блокировки
		$value = ["value" => $new_block_level];
		Type_System_Config::init()->set(Type_Antispam_Leveled_Main::CONFIG_BLOCK_LEVEL_KEY, $value);
	}
}