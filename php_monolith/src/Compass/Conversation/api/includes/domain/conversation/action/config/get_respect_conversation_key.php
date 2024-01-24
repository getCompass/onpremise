<?php

namespace Compass\Conversation;

/**
 * Action для получения конфига ключа группы Благодарности
 */
class Domain_Conversation_Action_Config_GetRespectConversationKey {

	/**
	 * Получаем из конфига значение ключа группы Благодарности
	 *
	 * @throws \queryException
	 */
	public static function do():array {

		$config = Type_Conversation_Config::init();

		$value = $config->get(Domain_Company_Entity_Config::RESPECT_CONVERSATION_KEY_NAME);

		// проверяем если не существует настройки, то создаём её
		if (!isset($value["value"])) {

			$time   = time();
			$value  = ["value" => ""];
			$config = new Struct_Db_CompanyData_CompanyConfig(Domain_Company_Entity_Config::RESPECT_CONVERSATION_KEY_NAME, $time, $time, $value);

			Gateway_Db_CompanyData_CompanyConfig::insert($config);
		}

		return $value;
	}
}
