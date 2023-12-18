<?php

namespace Compass\Pivot;

/**
 * Основной класс для получения хендлеров по версиям
 */
class Domain_App_Entity_Rule_Main {

	// текущая версия хендлера
	protected const _CURRENT_VERSION = 2;

	/**
	 * Получить хендлер
	 *
	 * @param int $version
	 *
	 * @return Domain_App_Entity_Rule
	 * @throws Domain_App_Exception_Rule_UnknownHandler
	 */
	public static function getHandler(int $version = self::_CURRENT_VERSION):Domain_App_Entity_Rule {

		$feature_class = __NAMESPACE__ . "\Domain_App_Entity_Rule_V$version";

		if (!class_exists($feature_class)) {
			throw new Domain_App_Exception_Rule_UnknownHandler("unknown handler");
		}

		return new $feature_class();
	}
}