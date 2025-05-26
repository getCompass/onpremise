<?php

namespace Compass\Thread;

/**
 * Action для получения конфига настройки показывать ли статус просмотра сообщения
 */
class Domain_Company_Action_Config_GetShowMessageReadStatus {

	/**
	 * выполняем
	 */
	public static function do():int {

		$config = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::SHOW_MESSAGE_READ_STATUS);

		// проверяем если не существует настройки, то устанавливаем дефолтное значение
		if (!isset($config["value"])) {
			$config = ["value" => Domain_Company_Entity_Config::CONFIG_DEFAULT_VALUE_LIST[Domain_Company_Entity_Config::SHOW_MESSAGE_READ_STATUS]];
		}

		return $config["value"];
	}
}
