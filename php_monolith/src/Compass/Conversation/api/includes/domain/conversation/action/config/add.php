<?php

namespace Compass\Conversation;

/**
 * Action для добавления конфига компании
 */
class Domain_Conversation_Action_Config_Add {

	/**
	 *
	 * @throws \parseException
	 * @throws \queryException
	 */
	public static function do(mixed $value, string $key):void {

		$time   = time();
		$value  = ["value" => $value];
		$config = new Struct_Db_CompanyData_CompanyConfig($key, $time, $time, $value);

		// пробуем добавить запись
		$row_id = Gateway_Db_CompanyData_CompanyConfig::insert($config);
		if ($row_id > 0) {
			return;
		}

		// если не получилось, то обновляем
		$config = Type_Conversation_Config::init();
		$set    = ["value" => $value];
		$config->set($key, $set);
	}
}
