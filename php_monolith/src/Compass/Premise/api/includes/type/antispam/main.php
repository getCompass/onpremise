<?php

namespace Compass\Premise;

/**
 * Базовый класс для уровневых блокировок
 */
abstract class Type_Antispam_Main {

	/**
	 * Есть ли необходимость проверять блокировку
	 *
	 */
	public static function needCheckIsBlocked():bool {

		if (isBackendTest() && !isNeedAntispam()) {
			return true;
		}

		return false;
	}
}