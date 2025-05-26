<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для валидации номера телефона
 */
class Domain_Phone_Entity_Validator {

	/**
	 * выбрасывает исключение, если номер забанен
	 *
	 * @param string $phone_number
	 *
	 * @throws ParseFatalException
	 */
	public static function assertBanned(string $phone_number):void {

		try {
			$phone_number_hash = Type_Hash_PhoneNumber::makeHash($phone_number);
			Domain_User_Entity_PhoneBanned::get($phone_number_hash);
		} catch (RowNotFoundException) {

			// все ок не забанен
			return;
		}
		throw new Domain_User_Exception_PhoneBanned();
	}
}
