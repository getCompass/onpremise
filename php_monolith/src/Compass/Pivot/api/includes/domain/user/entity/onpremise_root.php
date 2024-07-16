<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для работы с рут-пользователем онпремайза.
 */
class Domain_User_Entity_OnpremiseRoot {

	public const ONPREMISE_ROOT_USER_ID_KEY = "ONPREMISE_ROOT_USER_ID";

	public const ONPREMISE_ROOT_USER_SSO_LOGIN_KEY = "ONPREMISE_ROOT_USER_SSO_LOGIN_KEY";

	/**
	 * Получить user_id рут-пользователя.
	 *
	 */
	public static function getUserId():int {

		$conf = Type_System_Config::init()->getConf(self::ONPREMISE_ROOT_USER_ID_KEY);

		return $conf["value"] ?? -1;
	}

	/**
	 * Установить в конфиг user_id рут-пользователя.
	 *
	 * @throws ReturnFatalException
	 */
	public static function setUserId(int $root_user_id):void {

		if ($root_user_id < 1) {
			throw new ReturnFatalException("Incorrect root user_id: {$root_user_id}");
		}

		$value = ["value" => $root_user_id];
		Type_System_Config::init()->set(self::ONPREMISE_ROOT_USER_ID_KEY, $value);
	}

	/**
	 * Проверить, что пользователь НЕ является рут пользователем.
	 *
	 * @throws Domain_User_Exception_IsOnpremiseRoot
	 */
	public static function assertNotRootUserId(int $user_id):void {

		if ($user_id === self::getUserId()) {
			throw new Domain_User_Exception_IsOnpremiseRoot("this is root user on premise environment");
		}
	}

	/**
	 * Установить в конфиг sso логин рут-пользователя.
	 * В данном поле может быть почта/номер телефона/(при необходимости могут быть добавлены другие другие типы логинов аутентификации от SSO в дальнейшем)
	 *
	 * @throws ReturnFatalException
	 */
	public static function setSsoLogin(string $sso_login):void {

		if ($sso_login == "") {
			throw new ReturnFatalException("sso login name is empty");
		}

		$value = ["value" => $sso_login];
		Type_System_Config::init()->set(self::ONPREMISE_ROOT_USER_SSO_LOGIN_KEY, $value);
	}

	/**
	 * Получить из конфига SSO логин рут-пользователя.
	 */
	public static function getSsoLoginName():string|bool {

		$conf = Type_System_Config::init()->getConf(self::ONPREMISE_ROOT_USER_SSO_LOGIN_KEY);

		return $conf["value"] ?? false;
	}

	/**
	 * Проверить по списку что среди sso логинов есть от рут-пользователя.
	 * В данном списке может быть почта/номер телефона (при необходимости другие логины аутентификации от SSO в дальнейшем)
	 *
	 */
	public static function hasSsoLoginNameByList(array $sso_login_list):bool {

		foreach ($sso_login_list as $sso_login) {

			if (self::getSsoLoginName() === $sso_login) {
				return true;
			}
		}

		return false;
	}
}