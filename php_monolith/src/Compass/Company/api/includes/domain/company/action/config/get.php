<?php

namespace Compass\Company;

/**
 * Action для получения конфига компании
 */
class Domain_Company_Action_Config_Get {

	public static function do(string $key):array {

		$config = Type_Company_Config::init();

		return $config->get($key);
	}
}
