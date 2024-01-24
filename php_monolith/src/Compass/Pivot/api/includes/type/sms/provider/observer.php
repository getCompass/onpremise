<?php

namespace Compass\Pivot;

/**
 * Класс для работы с логикой наблюдателя над провайдером
 */
class Type_Sms_Provider_Observer {

	// -------------------------------------------------------
	// EXTRA
	// -------------------------------------------------------

	// версия схемы
	protected const _EXTRA_VERSION = 1;

	// структура схем различных версий с их историей
	protected const _EXTRA_SCHEMAS = [

		1 => [
			"last_processed_history_row_id" => 0, // идентификатор последней обработанной записи истории отправки смс
		],
	];

	/**
	 * Инициировать extra
	 *
	 */
	public static function initExtra():array {

		$schema            = self::_EXTRA_SCHEMAS[self::_EXTRA_VERSION];
		$schema["version"] = self::_EXTRA_VERSION;
		return $schema;
	}

	/**
	 * Устанавливаем идентификатор последней обработанной записи истории отправки смс
	 *
	 */
	public static function setLastProcessedHistoryRowId(array $extra, int $last_processed_history_row_id):array {

		$extra                                  = self::_getExtra($extra);
		$extra["last_processed_history_row_id"] = $last_processed_history_row_id;
		return $extra;
	}

	/**
	 * Получаем идентификатор последней обработанной записи истории отправки смс
	 *
	 */
	public static function getLastProcessedHistoryRowId(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["last_processed_history_row_id"];
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