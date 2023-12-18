<?php

namespace Compass\Company;

/**
 * Action для получения конфига необходимости ввода пин-кода
 */
class Domain_Company_Action_Config_GetIsPinRequired {

	/**
	 * Получаем конфиг для обязательной установки пин кода
	 *
	 * @throws \queryException
	 */
	public static function do():array {

		$config = Type_Company_Config::init();

		$value = $config->get(Domain_Company_Entity_Config::IS_PIN_REQUIRED);

		// проверяем если не существует настройки, то создаём её
		if (!isset($value["value"])) {

			$time   = time();
			$value  = ["value" => 0];
			$config = new Struct_Db_CompanyData_CompanyConfig(Domain_Company_Entity_Config::IS_PIN_REQUIRED, $time, $time, $value);

			Gateway_Db_CompanyData_CompanyConfig::insert($config);
		}

		return $value;
	}
}
