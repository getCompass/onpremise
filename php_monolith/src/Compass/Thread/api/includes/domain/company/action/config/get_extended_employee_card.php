<?php

namespace Compass\Thread;

/**
 * Action для получения конфига карточки
 */
class Domain_Company_Action_Config_GetExtendedEmployeeCard {

	/**
	 * Получаем включена ли расширенная карточка
	 *
	 */
	public static function do():int {

		$config = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::MODULE_EXTENDED_EMPLOYEE_CARD_KEY);

		// проверяем если не существует настройки, то устанавливаем дефолтное значение
		if (!isset($config["value"])) {
			$config = ["value" => 0];
		}

		return $config["value"];
	}
}
