<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * задача класса работать с конфигом ограничений на сервере
 */
class Type_Restrictions_Config
{
	// константы ограничений
	protected const _PHONE_CHANGE_ENABLED       = "phone_change_enabled";
	protected const _MAIL_CHANGE_ENABLED        = "mail_change_enabled";
	protected const _NAME_CHANGE_ENABLED        = "name_change_enabled";
	protected const _AVATAR_CHANGE_ENABLED      = "avatar_change_enabled";
	protected const _BADGE_CHANGE_ENABLED       = "badge_change_enabled";
	protected const _DESCRIPTION_CHANGE_ENABLED = "description_change_enabled";
	protected const _STATUS_CHANGE_ENABLED      = "status_change_enabled";
	protected const _PROFILE_DELETION_ENABLED   = "deletion_enabled";

	// список доступных ключей ограничений
	protected const _ALLOWED_RESTRICTIONS_KEY = [
		self::_PHONE_CHANGE_ENABLED,
		self::_MAIL_CHANGE_ENABLED,
		self::_NAME_CHANGE_ENABLED,
		self::_AVATAR_CHANGE_ENABLED,
		self::_BADGE_CHANGE_ENABLED,
		self::_DESCRIPTION_CHANGE_ENABLED,
		self::_STATUS_CHANGE_ENABLED,
		self::_PROFILE_DELETION_ENABLED,
	];

	// ключ для мока конфига
	protected const _MOCK_KEY = "TEST_MOCK_RESTRICTIONS_PROFILE";

	/**
	 * Разрешено ли менять номер телефона
	 */
	public static function isPhoneChangeEnabled(): bool
	{

		if (!ServerProvider::isOnPremise()) {
			return true;
		}

		$config = self::_getProfile();
		return $config[self::_PHONE_CHANGE_ENABLED] ?? true;
	}

	/**
	 * Разрешено ли менять почту
	 */
	public static function isMailChangeEnabled(): bool
	{

		if (!ServerProvider::isOnPremise()) {
			return true;
		}

		$config = self::_getProfile();
		return $config[self::_MAIL_CHANGE_ENABLED] ?? true;
	}

	/**
	 * Разрешено ли менять имя
	 */
	public static function isNameChangeEnabled(): bool
	{

		if (!ServerProvider::isOnPremise()) {
			return true;
		}

		$config = self::_getProfile();
		return $config[self::_NAME_CHANGE_ENABLED] ?? true;
	}

	/**
	 * Разрешено ли менять аватар
	 */
	public static function isAvatarChangeEnabled(): bool
	{

		if (!ServerProvider::isOnPremise()) {
			return true;
		}

		$config = self::_getProfile();
		return $config[self::_AVATAR_CHANGE_ENABLED] ?? true;
	}

	/**
	 * Разрешено ли менять бадж
	 */
	public static function isBadgeChangeEnabled(): bool
	{

		if (!ServerProvider::isOnPremise()) {
			return true;
		}

		$config = self::_getProfile();
		return $config[self::_BADGE_CHANGE_ENABLED] ?? true;
	}

	/**
	 * Разрешено ли менять описание
	 */
	public static function isDescriptionChangeEnabled(): bool
	{

		if (!ServerProvider::isOnPremise()) {
			return true;
		}

		$config = self::_getProfile();
		return $config[self::_DESCRIPTION_CHANGE_ENABLED] ?? true;
	}

	/**
	 * Разрешено ли менять статус
	 */
	public static function isStatusChangeEnabled(): bool
	{

		if (!ServerProvider::isOnPremise()) {
			return true;
		}

		$config = self::_getProfile();
		return $config[self::_STATUS_CHANGE_ENABLED] ?? true;
	}

	/**
	 * Разрешено ли удалять профиль пользователям
	 */
	public static function isProfileDeletionEnabled(): bool
	{

		if (!ServerProvider::isOnPremise()) {
			return true;
		}

		$config = self::_getProfile();
		return $config[self::_PROFILE_DELETION_ENABLED] ?? true;
	}

	/**
	 * Получаем ограничения профиля
	 */
	public static function getProfileRestrictions(): array
	{

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
