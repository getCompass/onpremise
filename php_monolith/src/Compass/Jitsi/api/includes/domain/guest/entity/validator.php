<?php

namespace Compass\Jitsi;

/**
 * класс содержит валидацию данных, вводимых гостем
 * @package Compass\Jitsi
 */
class Domain_Guest_Entity_Validator {

	/** @var int максимальная длина гостя */
	protected const _NAME_MAX_LENGTH = 40;

	/**
	 * валидируем имя гостя
	 *
	 * @throws Domain_Guest_Exception_InvalidName
	 */
	public static function validateName(string $guest_name):void {

		$len = mb_strlen($guest_name);
		if ($len < 1 || $len > self::_NAME_MAX_LENGTH) {
			throw new Domain_Guest_Exception_InvalidName();
		}
	}
}