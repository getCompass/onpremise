<?php

namespace Compass\Pivot;

/**
 * Класс для работы с флагами стран
 */
class Domain_User_Entity_Flag {

	/**
	 * Получаем список флагов стран
	 *
	 */
	public static function getCountryFlagList():array {

		return getSystemConfig("FLAG_COUNTRY_LIST");
	}
}