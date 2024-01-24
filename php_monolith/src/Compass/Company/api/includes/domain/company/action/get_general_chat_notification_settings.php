<?php

namespace Compass\Company;

/**
 * Action для получения параметра, включены ли уведомления главного чата
 */
class Domain_Company_Action_GetGeneralChatNotificationSettings {

	/**
	 * Получаем, включены ли уведомления главного чата
	 *
	 * @throws \queryException
	 */
	public static function do():int {

		$config = Type_Company_Config::init();

		$value = $config->get(Domain_Company_Entity_Config::GENERAL_CHAT_NOTIFICATIONS);

		// проверяем если не существует настройки, то создаём её
		if (!isset($value["value"])) {

			$time   = time();
			$value  = ["value" => Domain_Company_Entity_Config::CONFIG_DEFAULT_VALUE_LIST[Domain_Company_Entity_Config::GENERAL_CHAT_NOTIFICATIONS]];
			$config = new Struct_Db_CompanyData_CompanyConfig(Domain_Company_Entity_Config::GENERAL_CHAT_NOTIFICATIONS, $time, $time, $value);

			Gateway_Db_CompanyData_CompanyConfig::insert($config);
		}

		return $value["value"];
	}
}
