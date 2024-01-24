<?php

namespace Compass\Pivot;

/**
 * Класс для взаимодействия с номерами телефонов.
 */
class Domain_User_Entity_Phone {

	/**
	 * Получаем запись с номером телефона пользователя
	 *
	 * @throws cs_PhoneNumberNotFound
	 */
	public static function getUserPhone(string $phone_number):Struct_Db_PivotPhone_PhoneUniq {

		try {
			return Gateway_Db_PivotPhone_PhoneUniqList::getOneWithUserId(Type_Hash_PhoneNumber::makeHash($phone_number));
		} catch (\BaseFrame\Exception\Gateway\RowNotFoundException) {
			throw new cs_PhoneNumberNotFound("there is no record for passed phone number");
		}
	}

	/**
	 * Получить user_id владельца номера телефона
	 *
	 * @throws cs_PhoneNumberNotFound
	 */
	public static function getUserIdByPhone(string $phone_number):int {

		return self::getUserPhone($phone_number)->user_id;
	}

	/**
	 * Получить номер телефона пользователя
	 *
	 * @throws cs_UserPhoneSecurityNotFound
	 */
	public static function getPhoneByUserId(string $user_id):string {

		try {
			$security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_UserPhoneSecurityNotFound();
		}

		return $security->phone_number;
	}

	/**
	 * удаляем запись
	 */
	public static function delete(int $user_id):void {

		Gateway_Db_PivotUser_UserSecurity::delete($user_id);
	}
}