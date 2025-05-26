<?php

namespace Compass\Pivot;

/**
 * Класс для работы с проверкой
 */
class Domain_Subnet_Entity_Check {

	public const STATUS_NEED_CHECK = 0;
	public const STATUS_CHECKED    = 1;

	protected const _EXTRA_VERSION = 1; // версия упаковщика
	protected const _EXTRA_SCHEMA  = [  // схема extra

		1 => [
			"response" => [],
		],
	];

	/**
	 * Создать новую структуру для extra
	 *
	 * @return array
	 */
	public static function initExtra():array {

		return [
			"version" => self::_EXTRA_VERSION,
			"extra"   => self::_EXTRA_SCHEMA[self::_EXTRA_VERSION],
		];
	}

	/**
	 * Сохраняем ответ внешнего сервиса
	 *
	 * @param array $extra
	 * @param array $response
	 *
	 * @return array
	 */
	public static function setResponse(array $extra, array $response):array {

		$extra                      = self::_getExtra($extra);
		$extra["extra"]["response"] = $response;

		return $extra;
	}

	/**
	 * Получаем ответ внешнего сервиса
	 *
	 * @param array $extra
	 *
	 * @return array
	 */
	public static function getResponse(array $extra):array {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["response"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получить актуальную структуру для extra
	 *
	 * @param array $extra
	 *
	 * @return array
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