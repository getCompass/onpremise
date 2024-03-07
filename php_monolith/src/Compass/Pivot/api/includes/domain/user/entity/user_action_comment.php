<?php

namespace Compass\Pivot;

/**
 * Класс для работы с действием над профилем пользователя
 *
 * Class Domain_User_Entity_UserActionComment
 */
class Domain_User_Entity_UserActionComment {

	public const USER_REGISTER       = 1; // пользователь зарегался
	public const USER_LOGIN          = 2; // пользователь залогинился
	public const PROFILE_DATA_CHANGE = 3; // пользователь сменил данные профиля
	public const DELETE_PROFILE      = 4; // пользователь удалил свой аккаунт

	/**
	 * добавляем действие
	 *
	 * @throws \queryException
	 */
	public static function addAction(int $user_id, int $type, array $extra):void {

		Gateway_Db_PivotHistoryLogs_UserActionHistory::insert($user_id, $type, time(), $extra);
	}

	/**
	 * Добавляем действие при регистрации пользователя
	 *
	 * @throws \queryException
	 */
	public static function addUserRegisterAction(int $user_id, string $phone_number, string $ip_address):void {

		$extra = self::initExtra();
		self::setData($extra, ["phone_number" => $phone_number, "ip_address" => $ip_address]);

		self::addAction($user_id, self::USER_REGISTER, $extra);
	}

	/**
	 * Добавляем действие при логине пользователя
	 *
	 * @throws \queryException
	 */
	public static function addUserLoginAction(int $user_id, int $auth_type, string $auth_parameteer, string $device_id, string $user_agent):void {

		$phone_number = Domain_User_Entity_AuthStory::isPhoneNumberAuth($auth_type) ? $auth_parameteer : "";
		$mail         = Domain_User_Entity_AuthStory::isMailAuth($auth_type) ? $auth_parameteer : "";

		$extra = self::initExtra();
		self::setData($extra, ["phone_number" => $phone_number, "mail" => $mail, "device_id" => $device_id, "user_agent" => $user_agent]);

		self::addAction($user_id, self::USER_LOGIN, $extra);
	}

	/**
	 * Добавляем действие при изменении профиля пользователя
	 *
	 * @throws \queryException
	 */
	public static function addProfileDataChangeAction(int $user_id, array $updated_data):void {

		$extra = self::initExtra();
		self::setData($extra, ["updated_data" => $updated_data]);

		self::addAction($user_id, self::PROFILE_DATA_CHANGE, $extra);
	}

	/**
	 * Добавляем действие при удалении аккаунта профиля
	 *
	 * @throws \queryException
	 */
	public static function addDeleteProfileAction(int $user_id, string $phone_number):void {

		$extra = self::initExtra();
		self::setData($extra, ["phone_number" => $phone_number]);

		self::addAction($user_id, self::DELETE_PROFILE, $extra);
	}

	// -------------------------------------------------------
	// EXTRA
	// -------------------------------------------------------

	protected const _EXTRA_VERSION = 1; // версия упаковщика
	protected const _EXTRA_SCHEMA  = [  // схема extra

		1 => [
			"data" => [],
		],
	];

	/**
	 * Создать новую структуру для extra
	 */
	public static function initExtra():array {

		return [
			"version" => self::_EXTRA_VERSION,
			"extra"   => self::_EXTRA_SCHEMA[self::_EXTRA_VERSION],
		];
	}

	/**
	 * Добавляем данные для extra
	 */
	public static function setData(array $extra, array $data):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["data"] = $data;

		return $extra;
	}

	/**
	 * Получить актуальную структуру для extra
	 */
	protected static function _getExtra(array $extra):array {

		// если версия не совпадает - дополняем её до текущей
		if ($extra["version"] != self::_EXTRA_VERSION) {

			$extra["extra"]   = array_merge(self::_EXTRA_SCHEMA[self::_EXTRA_VERSION], $extra["extra"]);
			$extra["version"] = self::_EXTRA_VERSION;
		}

		return $extra;
	}
}