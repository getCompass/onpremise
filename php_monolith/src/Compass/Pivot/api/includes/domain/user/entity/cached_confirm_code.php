<?php

namespace Compass\Pivot;

/**
 * Класс для кэширования проверочного кода в чистом виде
 */
class Domain_User_Entity_CachedConfirmCode {

	protected const _TYPE_AUTH                    = "auth";
	protected const _TYPE_AUTH_MAIL_FULL_SCENARIO = "auth_mail_full_scenario";
	protected const _TYPE_CHANGE_PHONE            = "change_phone";
	protected const _TYPE_ADD_PHONE               = "add_phone";
	protected const _TYPE_ADD_MAIL                = "add_mail";
	protected const _TYPE_RESET_PASSWORD_MAIL     = "reset_password_mail";
	protected const _TYPE_CHANGE_MAIL             = "change_mail";

	/**
	 * сохраняем в кэш проверочный код для аутентификации
	 */
	public static function storeAuthCode(string $confirm_code, int $life_time):void {

		self::_store(self::_TYPE_AUTH, $life_time, [
			"confirm_code" => $confirm_code,
		]);
	}

	/**
	 * сохраняем в кэш параметры full_scenario аутентификации через почту
	 */
	public static function storeMailAuthFullScenarioParams(string $confirm_code, string $password, int $life_time):void {

		self::_store(self::_TYPE_AUTH_MAIL_FULL_SCENARIO, $life_time, [
			"confirm_code" => $confirm_code,
			"password"     => $password,
		]);
	}

	/**
	 * получаем из кэша параметры full_scenario аутентификации через почту
	 *
	 * @throws cs_CacheIsEmpty
	 */
	public static function getMailAuthFullScenarioParams():array {

		$data = self::_get(self::_TYPE_AUTH_MAIL_FULL_SCENARIO);

		return [$data["confirm_code"], $data["password"]];
	}

	/**
	 * сохраняем в кэш проверочный код для смены номера телефона
	 *
	 */
	public static function storeChangePhoneCode(string $confirm_code, int $stage):void {

		self::_store(
			self::_TYPE_CHANGE_PHONE . "_" . $stage,
			Domain_User_Entity_ChangePhone_Story::EXPIRE_AFTER,
			[
				"confirm_code" => $confirm_code,
			]
		);
	}

	/**
	 * Сохраняем в кэш проверочный код для добавления номера телефона
	 */
	public static function storeAddPhoneCode(string $confirm_code, int $stage):void {

		self::_store(
			self::_TYPE_ADD_PHONE . "_" . $stage,
			Domain_User_Entity_Security_AddPhone_Story::EXPIRE_AFTER,
			[
				"confirm_code" => $confirm_code,
			]
		);
	}

	/**
	 * сохраняем в кэш проверочный код в чистом виде
	 *
	 */
	protected static function _store(string $type, int $expire_after, array $data):void {

		Type_Session_Main::setCache(self::_getKey($type), $data, $expire_after + 3 * 60);
	}

	/**
	 * получаем из кэша проверочный код для аутентификации
	 *
	 * @throws cs_CacheIsEmpty
	 */
	public static function getAuthCode():string {

		return self::_get(self::_TYPE_AUTH)["confirm_code"];
	}

	/**
	 * получаем из кэша проверочный код для смены номера телефона
	 *
	 * @throws cs_CacheIsEmpty
	 */
	public static function getChangePhoneCode(int $stage):string {

		return self::_get(self::_TYPE_CHANGE_PHONE . "_" . $stage)["confirm_code"];
	}

	/**
	 * Получаем из кэша проверочный код для добавления номера телефона
	 *
	 * @throws cs_CacheIsEmpty
	 */
	public static function getAddPhoneCode(int $stage):string {

		return self::_get(self::_TYPE_ADD_PHONE . "_" . $stage)["confirm_code"];
	}

	/**
	 * сохраняем в кэш параметры full_scenario при добавлении почты
	 */
	public static function storeAddMailFullScenarioParams(string $mail, string $confirm_code, string $password, int $life_time):void {

		self::_store($mail . self::_TYPE_ADD_MAIL, $life_time, [
			"confirm_code" => $confirm_code,
			"password"     => $password,
		]);
	}

	/**
	 * получаем из кэша параметры full_scenario при добавлении почты
	 *
	 * @throws cs_CacheIsEmpty
	 */
	public static function getAddMailFullScenarioParams(string $mail):array {

		$data = self::_get($mail . self::_TYPE_ADD_MAIL);

		return [$data["confirm_code"], $data["password"]];
	}

	/**
	 * сохраняем в кэш параметры при сбросе пароля
	 */
	public static function storeResetPasswordMailParams(string $mail, string $confirm_code, int $life_time):void {

		self::_store($mail . self::_TYPE_RESET_PASSWORD_MAIL, $life_time, [
			"confirm_code" => $confirm_code,
		]);
	}

	/**
	 * получаем из кэша параметры при сбросе пароля почты
	 *
	 * @throws cs_CacheIsEmpty
	 */
	public static function getConfirmCodeByResetPasswordMail(string $mail):string {

		$data = self::_get($mail . self::_TYPE_RESET_PASSWORD_MAIL);

		return $data["confirm_code"];
	}

	/**
	 * получаем из кэша проверочный код в чистом виде
	 *
	 * @throws cs_CacheIsEmpty
	 */
	protected static function _get(string $type):array {

		$result = Type_Session_Main::getCache(self::_getKey($type));
		if ($result === []) {
			throw new cs_CacheIsEmpty();
		}

		return $result;
	}

	/**
	 * получаем ключ для хранения в кэше проверочного кода в чистом виде
	 *
	 */
	protected static function _getKey(string $type):string {

		return self::class . "_pure_" . $type . "_confirm_code";
	}

	/**
	 * сохраняем в кэш параметры при смене почты
	 */
	public static function storeChangeMailParams(string $mail, string $confirm_code, int $life_time):void {

		self::_store($mail . self::_TYPE_CHANGE_MAIL, $life_time, [
			"confirm_code" => $confirm_code,
		]);
	}

	/**
	 * получаем из кэша параметры при смене почты
	 *
	 * @throws cs_CacheIsEmpty
	 */
	public static function getConfirmCodeByChangeMail(string $mail):string {

		$data = self::_get($mail . self::_TYPE_CHANGE_MAIL);

		return $data["confirm_code"];
	}

}