<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * задача класса работать с конфигом ограничений на сервере
 */
class Type_Restrictions_Config {

	protected const _PHONE_CHANGE_ENABLED       = "phone_change_enabled";
	protected const _MAIL_CHANGE_ENABLED        = "mail_change_enabled";
	protected const _NAME_CHANGE_ENABLED        = "name_change_enabled";
	protected const _AVATAR_CHANGE_ENABLED      = "avatar_change_enabled";
	protected const _BADGE_CHANGE_ENABLED       = "badge_change_enabled";
	protected const _DESCRIPTION_CHANGE_ENABLED = "description_change_enabled";
	protected const _STATUS_CHANGE_ENABLED      = "status_change_enabled";

	/**
	 * Разрешено ли менять номер телефона
	 * @return bool
	 */
	public static function isPhoneChangeEnabled():bool {

		if (!ServerProvider::isOnPremise()) {
			return true;
		}

		$config = self::_getProfile();
		return $config[self::_PHONE_CHANGE_ENABLED] ?? true;
	}

	/**
	 * Разрешено ли менять почту
	 * @return bool
	 */
	public static function isMailChangeEnabled():bool {

		if (!ServerProvider::isOnPremise()) {
			return true;
		}

		$config = self::_getProfile();
		return $config[self::_MAIL_CHANGE_ENABLED] ?? true;
	}

	/**
	 * Разрешено ли менять имя
	 * @return bool
	 */
	public static function isNameChangeEnabled():bool {

		if (!ServerProvider::isOnPremise()) {
			return true;
		}

		$config = self::_getProfile();
		return $config[self::_NAME_CHANGE_ENABLED] ?? true;
	}

	/**
	 * Разрешено ли менять аватар
	 * @return bool
	 */
	public static function isAvatarChangeEnabled():bool {

		if (!ServerProvider::isOnPremise()) {
			return true;
		}

		$config = self::_getProfile();
		return $config[self::_AVATAR_CHANGE_ENABLED] ?? true;
	}

	/**
	 * Разрешено ли менять бадж
	 * @return bool
	 */
	public static function isBadgeChangeEnabled():bool {

		if (!ServerProvider::isOnPremise()) {
			return true;
		}

		$config = self::_getProfile();
		return $config[self::_BADGE_CHANGE_ENABLED] ?? true;
	}

	/**
	 * Разрешено ли менять описание
	 * @return bool
	 */
	public static function isDescriptionChangeEnabled():bool {

		if (!ServerProvider::isOnPremise()) {
			return true;
		}

		$config = self::_getProfile();
		return $config[self::_DESCRIPTION_CHANGE_ENABLED] ?? true;
	}

	/**
	 * Разрешено ли менять статус
	 * @return bool
	 */
	public static function isStatusChangeEnabled():bool {

		if (!ServerProvider::isOnPremise()) {
			return true;
		}

		$config = self::_getProfile();
		return $config[self::_STATUS_CHANGE_ENABLED] ?? true;
	}

	/**
	 * Получаем ограничения профиля
	 * @return array
	 */
	public static function getProfileRestrictions():array {

		$profile_config = self::_getProfile();

		$output = [];
		foreach ($profile_config as $k => $v) {
			$output[$k] = (int) $v;
		}

		return $output;
	}

	/**
	 * Получить содержимое конфиг-файла
	 */
	protected static function _getProfile():array {

		// поскольку содержимое конфиг-файла не может поменяться нагорячую
		// то ничего не мешает положить его в глобальную переменную
		if (isset($GLOBALS[self::class])) {
			return $GLOBALS[self::class];
		}

		$GLOBALS[self::class] = getConfig("RESTRICTIONS_PROFILE");
		return $GLOBALS[self::class];
	}
}
