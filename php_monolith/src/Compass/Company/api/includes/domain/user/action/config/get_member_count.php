<?php

namespace Compass\Company;

/**
 * Класс для получения конфига с количеством пользователей
 */
class Domain_User_Action_Config_GetMemberCount {

	/**
	 * Получаем конфиг с количеством участников
	 */
	public static function do():int {

		$config = Type_Company_Config::init();

		// получаем значение
		$member_count = $config->get(Domain_Company_Entity_Config::MEMBER_COUNT)["value"] ?? 0;

		// если значение меньше 0, то исправим
		if ($member_count < 0) {
			$member_count = 0;
		}

		return $member_count;
	}
}
