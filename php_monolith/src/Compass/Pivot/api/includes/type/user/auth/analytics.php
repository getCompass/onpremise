<?php

namespace Compass\Pivot;

/**
 * Класс для основной работы по логированию авторизации пользователя
 */
class Type_User_Auth_Analytics {

	protected const _EVENT_KEY = "user_auth_analytics";

	// пишем аналитику в collector
	public static function save(int $user_id, string $user_agent, string $device_id, string $lang, string $server_time, string $time_zone, array $analytics_data = []):void {

		[$platform, $version, $is_compass] = self::_getDataForUserAgent($user_agent);

		Gateway_Bus_CollectorAgent::init()->log(self::_EVENT_KEY, [
			"uniq_key"       => self::makeHash($user_id . $platform . $version),
			"user_id"        => $user_id,
			"platform"       => $platform,
			"version"        => $version,
			"lang"           => $lang,
			"event_time"     => time(),
			"server_time"    => $server_time,
			"time_zone"      => $time_zone,
			"is_compass"     => $is_compass,
			"device_id"      => $device_id,
			"analytics_data" => toJson($analytics_data),
		]);
	}

	/**
	 * Хэшируем $value
	 */
	public static function makeHash(string $value):string {

		return self::_makeHash($value, SALT_PHONE_NUMBER);
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получаем данные из user-agent пользователя
	 *
	 * @throws cs_PlatformNotFound
	 */
	protected static function _getDataForUserAgent(string $user_agent):array {

		$user_agent = mb_strtolower($user_agent);
		$platform   = Type_Api_Platform::getPlatform($user_agent);

		$is_compass = strstr(mb_strtolower($user_agent), "Compass") === false ? 1 : 0;

		preg_match("#\((.*?)\)#", $user_agent, $match);
		$version = $match[1];

		return [$platform, $version, $is_compass];
	}

	/**
	 * Хэшируем значение
	 */
	protected static function _makeHash(mixed $value, string $salt):string {

		return hash_hmac("sha1", (string) $value, $salt);
	}
}