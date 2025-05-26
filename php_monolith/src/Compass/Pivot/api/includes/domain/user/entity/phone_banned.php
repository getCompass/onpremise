<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Gateway\RowNotFoundException;

/**
 * класс для работы с баном номера телефона
 */
class Domain_User_Entity_PhoneBanned {

	/**
	 * Получаем запись с баном номера телефона
	 *
	 * @param string $phone_number_hash
	 *
	 * @return Struct_Db_PivotPhone_PhoneBanned
	 * @throws ParseFatalException
	 * @throws RowNotFoundException
	 */
	public static function get(string $phone_number_hash):Struct_Db_PivotPhone_PhoneBanned {

		return Gateway_Db_PivotPhone_PhoneBanned::getOne($phone_number_hash);
	}
}