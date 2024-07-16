<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Request\ParamException;

/**
 * класс для валидирования
 * @package Compass\Jitsi
 */
class Type_Validator {

	/**
	 * проверяем, что в параметр флага 0/1 не передали левак
	 */
	public static function assertBoolFlag(int $flag):void {

		if (!in_array($flag, [0, 1])) {
			throw new ParamException("unexpected value");
		}
	}
}