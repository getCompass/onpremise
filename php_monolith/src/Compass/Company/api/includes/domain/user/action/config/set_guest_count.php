<?php

namespace Compass\Company;

/**
 *  Класс для изменения конфига с количеством гостей
 */
class Domain_User_Action_Config_SetGuestCount {

	/**
	 * Изменяем конфиг с количеством гостей
	 *
	 * @param int $value
	 *
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function do(int $value):void {

		$config = Type_Company_Config::init();

		$config->set(Domain_Company_Entity_Config::GUEST_COUNT, [
			"value"      => ["value" => $value],
			"updated_at" => time(),
		]);
	}
}
