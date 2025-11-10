<?php

namespace Compass\Company;

/**
 * задача класса работать с конфигом ограничений приложений
 */
class Domain_SmartApp_Entity_Restrictions {

	/**
	 * Получаем весь конфиг
	 * @return bool
	 */
	public static function isCreateFromCatalogDisabled():bool {

		return self::_getConfig()["is_create_from_catalog_disabled"];
	}

	/**
	 * Получаем весь конфиг
	 * @return bool
	 */
	public static function isCreateCustomSmartAppsDisabled():bool {

		return self::_getConfig()["is_create_custom_smart_apps_disabled"];
	}

	/**
	 * Получить содержимое конфиг-файла
	 */
	protected static function _getConfig():array {

		$key = self::class . "_restrictions";

		// поскольку содержимое конфиг-файла не может поменяться нагорячую
		// то ничего не мешает положить его в глобальную переменную
		if (isset($GLOBALS[$key])) {
			return $GLOBALS[$key];
		}

		$GLOBALS[$key] = getConfig("SMARTAPPS_RESTRICTIONS");
		return $GLOBALS[$key];
	}
}
