<?php

namespace Compass\Conversation;

/**
 * Action для получения конфига настройки добавления пользователя в Главный чат при вступлении
 */
class Domain_Company_Action_Config_GetAddToGeneralChatOnHiring {

	/**
	 * выполняем
	 */
	public static function do():int {

		$config = Domain_Company_Action_Config_Get::do(Domain_Company_Entity_Config::ADD_TO_GENERAL_CHAT_ON_HIRING);

		// проверяем если не существует настройки, то устанавливаем дефолтное значение
		if (!isset($config["value"])) {
			$config = ["value" => Domain_Company_Entity_Config::CONFIG_DEFAULT_VALUE_LIST[Domain_Company_Entity_Config::ADD_TO_GENERAL_CHAT_ON_HIRING]];
		}

		return $config["value"];
	}
}
