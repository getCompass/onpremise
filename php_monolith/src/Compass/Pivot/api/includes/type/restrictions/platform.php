<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * задача класса работать с конфигом ограничений платформы на сервере
 */
class Type_Restrictions_Platform {

	protected const _IS_DESKTOP_PROHIBITED = "is_desktop_prohibited";
	protected const _IS_IOS_PROHIBITED     = "is_ios_prohibited";
	protected const _IS_ANDROID_PROHIBITED = "is_android_prohibited";

	/**
	 * Запрещено ли работать с ПК
	 * @return bool
	 */
	public static function isDesktopProhibited():bool {

		if (!ServerProvider::isOnPremise()) {
			return false;
		}

		$config = self::_getConfig();
		return $config[self::_IS_DESKTOP_PROHIBITED] ?? false;
	}

	/**
	 * Запрещено ли работать с iOS
	 * @return bool
	 */
	public static function isIosProhibited():bool {

		if (!ServerProvider::isOnPremise()) {
			return false;
		}

		$config = self::_getConfig();
		return $config[self::_IS_IOS_PROHIBITED] ?? false;
	}

	/**
	 * Запрещено ли работать с Android
	 * @return bool
	 */
	public static function isAndroidProhibited():bool {

		if (!ServerProvider::isOnPremise()) {
			return false;
		}

		$config = self::_getConfig();
		return $config[self::_IS_ANDROID_PROHIBITED] ?? false;
	}

	/**
	 * Получить содержимое конфиг-файла
	 */
	protected static function _getConfig():array {

		// поскольку содержимое конфиг-файла не может поменяться нагорячую
		// то ничего не мешает положить его в глобальную переменную
		if (isset($GLOBALS[self::class])) {
			return $GLOBALS[self::class];
		}

		$GLOBALS[self::class] = getConfig("RESTRICTIONS_PLATFORM");
		return $GLOBALS[self::class];
	}
}
