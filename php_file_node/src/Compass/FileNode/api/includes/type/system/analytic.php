<?php

namespace Compass\FileNode;

use JetBrains\PhpStorm\Pure;

/**
 * Класс для основной работы по логированию системных тасков компании
 */
class Type_System_Analytic {

	public const TYPE_POSTUPLOAD_VIDEO_TIME = 40; // тип аналитики - постобработка видео

	protected const _EVENT_KEY = "company_analytics";

	// пишем аналитику в collector
	public static function save(int $company_id, string $domino_id, int $type, array $analytics_data = []):void {

		Gateway_Bus_CollectorAgent::init()->log(self::_EVENT_KEY, [
			"uniq_key"       => self::makeHash(generateRandomString()),
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

		return self::_makeHash($value, SALT_ANALYTIC);
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