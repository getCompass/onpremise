<?php

namespace Compass\Pivot;

/**
 * Класс для кэширования проверочного кода в чистом виде
 */
class Domain_User_Entity_CachedConfirmCode {

	protected const _TYPE_AUTH                    = "auth";
	protected const _TYPE_AUTH_MAIL_FULL_SCENARIO = "auth_mail_full_scenario";
	protected const _TYPE_CHANGE_PHONE            = "change_phone";

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
}