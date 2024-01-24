<?php

namespace Compass\Company;

/**
 * Action для изменения конфига компании
 */
class Domain_Company_Action_Config_Set {

	/**
	 *
	 * @throws \parseException
	 */
	public static function do(mixed $value, string $key):void {

		$config = Type_Company_Config::init();

		$config->set($key, [
			"value"      => $value,
			"updated_at" => time(),
		]);
	}
}
