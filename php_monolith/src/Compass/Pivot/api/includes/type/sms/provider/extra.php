<?php

namespace Compass\Pivot;

/**
 * Класс для работы с extra провайдера
 */
class Type_Sms_Provider_Extra {

	// версия схемы
	protected const _EXTRA_VERSION = 1;

	// структура схем различных версий с их историей
	protected const _EXTRA_SCHEMAS = [

		1 => [
			"relevant_grade" => 100, // релевантная оценка провайдера (от 1 до 100, при создании 100)
		],
	];

	/**
	 * Инициировать extra
	 *
	 */
	public static function init():array {

		$schema            = self::_EXTRA_SCHEMAS[self::_EXTRA_VERSION];
		$schema["version"] = self::_EXTRA_VERSION;
		return $schema;
	}

	/**
	 * Устанавливаем релевантную оценку
	 *
	 */
	public static function setRelevantGrade(array $extra, int $relevant_grade):array {

		$extra                   = self::_getExtra($extra);
		$extra["relevant_grade"] = $relevant_grade;
		return $extra;
	}

	/**
	 * Получаем релевантную оценку
	 *
	 */
	public static function getRelevantGrade(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["relevant_grade"];
	}

	/**
	 * Актуализируем структуру extra
	 *
	 */
	protected static function _getExtra(array $extra):array {

		// сравниваем версию пришедшей extra с текущей
		if ($extra["version"] != self::_EXTRA_VERSION) {

			// сливаем текущую версию extra и ту, что пришла
			$extra["extra"]   = array_merge(self::_EXTRA_SCHEMAS[self::_EXTRA_VERSION], $extra);
			$extra["version"] = self::_EXTRA_VERSION;
		}
		return $extra;
	}
}