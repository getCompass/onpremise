<?php

namespace Compass\Pivot;

use JetBrains\PhpStorm\Pure;

/**
 * Класс для основной работы по логированию системных тасков компании
 */
class Type_System_Analytic {

	public const TYPE_HIBERNATE                     = 1; // тип аналитики - начало гибернации
	public const TYPE_POST_HIBERNATE                = 2; // тип аналитики - конец гибернации
	public const TYPE_AWAKE                         = 3; // тип аналитики - начало пробуждение
	public const TYPE_POST_AWAKE                    = 4; // тип аналитики - конец пробуждения
	public const TYPE_MIGRATION                     = 5; // тип аналитики - начало миграции
	public const TYPE_POST_MIGRATION                = 6; // тип аналитики - конец миграции
	public const TYPE_RELOCATION                    = 7; // тип аналитики - начало релокации
	public const TYPE_POST_RELOCATION               = 8; // тип аналитики - конец релокации
	public const TYPE_CREATING                      = 9; // тип аналитики - начало создания компании
	public const TYPE_POST_CREATING                 = 10; // тип аналитики - конец создания компании
	public const TYPE_COMPANY_FULL                  = 11; // тип аналитики - компаний всего
	public const TYPE_COMPANY_ACTIVE                = 12; // тип аналитики - компания активна
	public const TYPE_COMPANY_HIBERNATION           = 13; // тип аналитики - компания в гибернации
	public const TYPE_COMPANY_HOT                   = 14; // тип аналитики - компания в горячем списке
	public const TYPE_COMPANY_DELETED               = 15; // тип аналитики - компания не принимает запросы
	public const TYPE_COMPANY_PURGED                = 16; // тип аналитики - компания удалена
	public const TYPE_COMPANY_MIGRATION_ACTUAL      = 17; // тип аналитики - миграция актуальна
	public const TYPE_COMPANY_MIGRATION_NOT_ACTUAL  = 18; // тип аналитики - миграция не актуальна
	public const TYPE_CHECK_STATUS_IOS              = 19; // тип аналитики - проверка статуса с ios
	public const TYPE_CHECK_STATUS_ANDROID          = 20; // тип аналитики - проверка статуса с android
	public const TYPE_CHECK_STATUS_ELECTRON         = 21; // тип аналитики - проверка статуса с electron
	public const TYPE_HIBERNATE_AVERAGE_TIME        = 22; // тип аналитики - среднее время гибернации
	public const TYPE_HIBERNATE_MAX_TIME            = 22; // тип аналитики - максимальное время гибернации
	public const TYPE_HIBERNATE_MIN_TIME            = 23; // тип аналитики - минимальное время гибернации
	public const TYPE_AWAKE_AVERAGE_TIME            = 24; // тип аналитики - среднее время пробуждения
	public const TYPE_AWAKE_MAX_TIME                = 25; // тип аналитики - максимальное время пробуждения
	public const TYPE_AWAKE_MIN_TIME                = 26; // тип аналитики - минимальное время пробуждения
	public const TYPE_MIGRATION_AVERAGE_TIME        = 27; // тип аналитики - среднее время миграции
	public const TYPE_MIGRATION_MAX_TIME            = 28; // тип аналитики - максимальное время миграции
	public const TYPE_MIGRATION_MIN_TIME            = 29; // тип аналитики - минимальное время миграции
	public const TYPE_RELOCATION_AVERAGE_TIME       = 30; // тип аналитики - среднее время релокации
	public const TYPE_RELOCATION_MAX_TIME           = 31; // тип аналитики - максимальное время релокации
	public const TYPE_RELOCATION_MIN_TIME           = 32; // тип аналитики - минимальное время релокации
	public const TYPE_CREATING_MAX_TIME             = 33; // тип аналитики - максимальное время перевода компании в создание
	public const TYPE_CREATING_AVERAGE_TIME         = 34; // тип аналитики - среднее время перевода компании в создание
	public const TYPE_UNBIND_PORT_ON_DELETE_COMPANY = 34; // тип аналитики - удаляем порт при удалении компании

	protected const _EVENT_KEY = "company_analytics";

	// пишем аналитику в collector
	public static function save(int $company_id, string $domino_id, int $type, array $analytics_data = []):void {

		Gateway_Bus_CollectorAgent::init()->log(self::_EVENT_KEY, [
			"uniq_key"       => self::makeHash($company_id . $type),
			"domino_id"      => $domino_id,
			"company_id"     => $company_id,
			"type"           => $type,
			"event_time"     => time(),
			"analytics_data" => toJson($analytics_data),
		]);
	}

	/**
	 * Хэшируем $value
	 */
	#[Pure]
	public static function makeHash(string $value):string {

		return self::_makeHash($value, SALT_PHONE_NUMBER);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Хэшируем значение
	 */
	protected static function _makeHash(mixed $value, string $salt):string {

		return hash_hmac("sha1", (string) $value, $salt);
	}
}