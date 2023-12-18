<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для работы с рут-пользователем онпремайза.
 */
class Domain_User_Entity_OnpremiseRoot {

	public const ONPREMISE_ROOT_USER_ID_KEY = "ONPREMISE_ROOT_USER_ID";

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
}