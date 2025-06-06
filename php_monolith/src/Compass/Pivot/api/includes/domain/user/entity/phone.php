<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

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
	 * @throws ParseFatalException
	 */
	public static function getPhoneByUserId(string $user_id):string {

		try {
			$security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);
		} catch (\cs_RowIsEmpty) {
			throw new cs_UserPhoneSecurityNotFound();
		}

		if (mb_strlen($security->phone_number) === 0) {
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

	/**
	 * указывает на наличие номера телефона
	 */
	public static function hasPhoneNumber(Struct_Db_PivotUser_UserSecurity $user_security):bool {

		if (mb_strlen($user_security->phone_number) > 0) {
			return true;
		}

		return false;
	}

	/**
	 * получаем маску номера телефона
	 */
	public static function getPhoneNumberMask(string $phone_number):string {

		$phone_number_obj = new \BaseFrame\System\PhoneNumber($phone_number);
		return $phone_number_obj->obfuscate();
	}

	/**
	 * проверяем, что пользователь не был зарегистрирован через SSO
	 *
	 * @throws Domain_User_Exception_Security_UserWasRegisteredBySso
	 * @throws ParseFatalException
	 * @throws \BaseFrame\Exception\Domain\ReturnFatalException
	 */
	public static function assertUserWasNotRegisteredBySso(int $user_id):void {

		// на saas данная проверка не требуется
		if (ServerProvider::isSaas()) {
			return;
		}

		if (Gateway_Socket_Federation::hasSsoUserRelationship($user_id) && !Domain_User_Entity_Auth_Config::isAuthorizationAlternativeEnabled()) {
			throw new Domain_User_Exception_Security_UserWasRegisteredBySso();
		}
	}

	/**
	 * Проверяем что номер телефона есть у пользователя
	 *
	 * @throws cs_UserPhoneSecurityNotFound
	 */
	public static function assertAlreadyExistPhoneNumber(Struct_Db_PivotUser_UserSecurity $user_security):void {

		if (self::hasPhoneNumber($user_security)) {
			return;
		}

		throw new cs_UserPhoneSecurityNotFound("phone not exist in user");
	}

	/**
	 * отвязываем номер телефона
	 *
	 * @throws ParseFatalException
	 * @throws \cs_RowIsEmpty
	 * @throws \parseException
	 * @throws \returnException
	 */
	public static function unbind(int $user_id):void {

		// проверяем наличие номера телефона
		$user_security = Gateway_Db_PivotUser_UserSecurity::getOne($user_id);

		// если номер не привязан, то ничего не делаем
		if ($user_security->phone_number === "") {
			return;
		}

		// хэш-сумма номера
		$phone_number_hash = Type_Hash_PhoneNumber::makeHash($user_security->phone_number);

		Gateway_Db_PivotPhone_Main::beginTransaction();

		// открепляем почту
		Gateway_Db_PivotPhone_PhoneUniqList::set($phone_number_hash, [
			"user_id"           => 0,
			"has_sso_account"   => 0,
			"last_unbinding_at" => time(),
			"updated_at"        => time(),
		]);

		// обновляем user_security
		Gateway_Db_PivotUser_UserSecurity::set($user_id, [
			"phone_number" => "",
			"updated_at"   => time(),
		]);

		Gateway_Db_PivotPhone_Main::commitTransaction();
	}
}