<?php

namespace Compass\Pivot;

/**
 * класс для работы с extra send_queue
 */
class Type_Sms_Queue_Extra {

	// версия схемы
	protected const _EXTRA_VERSION = 1;

	// структура схем различных версий с их историей
	protected const _EXTRA_SCHEMAS = [

		1 => [
			"excluded_provider_list" => [], // массив с идентификаторами исключенных провайдеров
			"provider_sms_id"        => "", // идентификатор смс в контексте провайдера через которого происходила отправка
			"last_history_row_id"    => 0,  // идентификатор записи в таблице с историей отправки sms
			"resend_for_sms_id"      => "", // идентификатор sms_id, для которого пользователь запросил переотправку
		],

		2 => [
			"excluded_provider_list" => [], // массив с идентификаторами исключенных провайдеров
			"provider_sms_id"        => "", // идентификатор смс в контексте провайдера через которого происходила отправка
			"last_history_row_id"    => 0,  // идентификатор записи в таблице с историей отправки sms
			"resend_for_sms_id"      => "", // идентификатор sms_id, для которого пользователь запросил переотправку
			"story_type"             => 0, // тип flow, для которого производится отправка сообщения (нужно в аналитике)
			"story_id"               => "", // *_map ID flow, для которого производится отправка сообщения (нужно в аналитике)
		],
	];

	/**
	 * Инициировать extra
	 */
	public static function init(array $excluded_provider_list, string $resend_for_sms_id, int $story_type, string $story_id):array {

		$schema                           = self::_EXTRA_SCHEMAS[self::_EXTRA_VERSION];
		$schema["version"]                = self::_EXTRA_VERSION;
		$schema["excluded_provider_list"] = $excluded_provider_list;
		$schema["resend_for_sms_id"]      = $resend_for_sms_id;
		$schema["story_type"]             = $story_type;
		$schema["story_id"]               = $story_id;

		return $schema;
	}

	/**
	 * Устанавливаем массив с идентификаторами исключенных провайдеров
	 *
	 */
	public static function setExcludedProviderList(array $extra, array $excluded_provider_list):array {

		$extra                           = self::_getExtra($extra);
		$extra["excluded_provider_list"] = $excluded_provider_list;
		return $extra;
	}

	/**
	 * Получаем массив с идентификаторами исключенных провайдеров
	 *
	 */
	public static function getExcludedProviderList(array $extra):array {

		$extra = self::_getExtra($extra);
		return $extra["excluded_provider_list"];
	}

	/**
	 * Записать идентификатор смс в контексте провайдера через которого происходила отправка
	 *
	 */
	public static function setProviderSmsId(array $extra, string $provider_sms_id):array {

		$extra                    = self::_getExtra($extra);
		$extra["provider_sms_id"] = $provider_sms_id;
		return $extra;
	}

	/**
	 * Получить идентификатор смс в контексте провайдера через которого происходила отправка
	 *
	 */
	public static function getProviderSmsId(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["provider_sms_id"];
	}

	/**
	 * Записать идентификатор записи в таблице с историей отправки sms
	 *
	 */
	public static function setLastHistoryRowId(array $extra, int $history_row_id):array {

		$extra                        = self::_getExtra($extra);
		$extra["last_history_row_id"] = $history_row_id;
		return $extra;
	}

	/**
	 * Получить идентификатор записи в таблице с историей отправки sms
	 *
	 */
	public static function getLastHistoryRowId(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["last_history_row_id"];
	}

	/**
	 * Записать идентификатор прошлого sms_id, для которого пользователь запросил переотправку
	 *
	 */
	public static function setResendForSmsId(array $extra, int $resend_after_sms_id):array {

		$extra                      = self::_getExtra($extra);
		$extra["resend_for_sms_id"] = $resend_after_sms_id;
		return $extra;
	}

	/**
	 * Получить идентификатор прошлого sms_id, для которого пользователь запросил переотправку
	 *
	 */
	public static function getResendForSmsId(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["resend_for_sms_id"];
	}

	/**
	 * Получаем тип действия, для которого была запрошена отправка смс
	 */
	public static function getStoryType(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["story_type"];
	}

	/**
	 * Получаем ID действия, для которого была запрошена отправка смс
	 */
	public static function getStoryId(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["story_id"];
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