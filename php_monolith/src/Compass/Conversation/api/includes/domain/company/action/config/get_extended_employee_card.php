<?php

namespace Compass\Conversation;

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
			$config = ["value" => Domain_Company_Entity_Config::CONFIG_DEFAULT_VALUE_LIST[Domain_Company_Entity_Config::MODULE_EXTENDED_EMPLOYEE_CARD_KEY]];
		}

		return $config["value"];
	}
}
