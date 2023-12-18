<?php

namespace Compass\Pivot;

/**
 * Класс для кэширования проверочного кода в чистом виде
 */
class Domain_User_Entity_CachedConfirmCode {

	protected const _TYPE_AUTH         = "auth";
	protected const _TYPE_CHANGE_PHONE = "change_phone";

	/**
	 * сохраняем в кэш проверочный код для аутентификации
	 *
	 */
	public static function storeAuthCode(string $confirm_code):void {

		self::_storeCode(self::_TYPE_AUTH, Domain_User_Entity_AuthStory::EXPIRE_AT, $confirm_code);
	}

	/**
	 * сохраняем в кэш проверочный код для смены номера телефона
	 *
	 */
	public static function storeChangePhoneCode(string $confirm_code, int $stage):void {

		self::_storeCode(
			self::_TYPE_CHANGE_PHONE . "_" . $stage,
			Domain_User_Entity_ChangePhone_Story::EXPIRE_AFTER,
			$confirm_code
		);
	}

	/**
	 * сохраняем в кэш проверочный код в чистом виде
	 *
	 */
	protected static function _storeCode(string $type, int $expire_after, string $confirm_code):void {

		Type_Session_Main::setCache(self::_getKey($type), [
			"confirm_code" => $confirm_code,
		], $expire_after + 3 * 60);
	}

	/**
	 * получаем из кэша проверочный код для аутентификации
	 *
	 * @throws cs_CacheIsEmpty
	 */
	public static function getAuthCode():string {

		return self::_getCode(self::_TYPE_AUTH);
	}

	/**
	 * получаем из кэша проверочный код для смены номера телефона
	 *
	 * @throws cs_CacheIsEmpty
	 */
	public static function getChangePhoneCode(int $stage):string {

		return self::_getCode(self::_TYPE_CHANGE_PHONE . "_" . $stage);
	}

	/**
	 * получаем из кэша проверочный код в чистом виде
	 *
	 * @throws cs_CacheIsEmpty
	 */
	protected static function _getCode(string $type):string {

		$result = Type_Session_Main::getCache(self::_getKey($type));
		if ($result === []) {
			throw new cs_CacheIsEmpty();
		}

		return $result["confirm_code"];
	}

	/**
	 * получаем ключ для хранения в кэше проверочного кода в чистом виде
	 *
	 */
	protected static function _getKey(string $type):string {

		return self::class . "_pure_" . $type . "_confirm_code";
	}
}