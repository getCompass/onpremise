<?php

namespace Compass\Company;

/**
 * Класс для получения конфига с количеством гостей
 */
class Domain_User_Action_Config_GetGuestCount {

	/**
	 * Получаем конфиг с количеством гостей
	 */
	public static function do():int {

		$config = Type_Company_Config::init();

		// получаем значение
		$guest_count = $config->get(Domain_Company_Entity_Config::GUEST_COUNT)["value"] ?? 0;

		// если значение меньше 0, то исправим
		if ($guest_count < 0) {
			$guest_count = 0;
		}

		return $guest_count;
	}
}
