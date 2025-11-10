<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;

/**
 * Класс для взаимодействия с smart app
 */
class Domain_SmartApp_Entity_SmartApp {

	// список статусов приложения
	public const STATUS_ENABLE = 1; // приложение активно
	public const STATUS_DELETE = 2; // приложение удалено

	// -------------------------------------------------------
	// EXTRA SCHEMA
	// -------------------------------------------------------

	protected const _EXTRA_VERSION = 1; // версия упаковщика
	protected const _EXTRA_SCHEMA  = [  // схема extra

		1 => [
			"title"                      => "",
			"deleted_at"                 => 0,
			"avatar_file_key"            => "",
			"url"                        => "",
			"is_open_in_new_windows"     => 0,
			"is_notifications_enabled"   => 0,
			"is_sound_enabled"           => 0,
			"is_background_work_enabled" => 0,
			"size"                       => "",
			"public_key"                 => "",
			"private_key"                => "",
		],
	];

	/**
	 * создаём новую структуру для extra
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
	 * установим название приложения
	 */
	public static function setTitle(array $extra, string $title):array {

		$extra                   = self::_getExtra($extra);
		$extra["extra"]["title"] = $title;
		return $extra;
	}

	/**
	 * получим название приложения
	 */
	public static function getTitle(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["title"];
	}

	/**
	 * установим время когда удалили приложение
	 */
	public static function setDeletedAt(array $extra, int $deleted_at):array {

		$extra                        = self::_getExtra($extra);
		$extra["extra"]["deleted_at"] = $deleted_at;
		return $extra;
	}

	/**
	 * получим временную метку удаления приложения
	 */
	public static function getDeletedAt(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["deleted_at"];
	}

	/**
	 * установим file_key аватарки приложения
	 */
	public static function setAvatarFileKey(array $extra, string $avatar_file_key):array {

		$extra                             = self::_getExtra($extra);
		$extra["extra"]["avatar_file_key"] = $avatar_file_key;
		return $extra;
	}

	/**
	 * получим file_key аватарки приложения
	 */
	public static function getAvatarFileKey(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["avatar_file_key"];
	}

	/**
	 * установим url приложения
	 */
	public static function setUrl(array $extra, string $url):array {

		$extra                 = self::_getExtra($extra);
		$extra["extra"]["url"] = $url;
		return $extra;
	}

	/**
	 * получим url приложения
	 */
	public static function getUrl(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["url"];
	}

	/**
	 * установим флаг открывать ли приложение в новом окне
	 */
	public static function setFlagOpenInNewWindows(array $extra, int $is_open_in_new_windows):array {

		$extra                                    = self::_getExtra($extra);
		$extra["extra"]["is_open_in_new_windows"] = $is_open_in_new_windows;
		return $extra;
	}

	/**
	 * получим флаг открывать ли приложение в новом окне
	 */
	public static function getFlagOpenInNewWindows(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["is_open_in_new_windows"];
	}

	/**
	 * установим флаг влючены ли уведомления
	 */
	public static function setFlagNotificationsEnabled(array $extra, int $is_notifications_enabled):array {

		$extra                                      = self::_getExtra($extra);
		$extra["extra"]["is_notifications_enabled"] = $is_notifications_enabled;
		return $extra;
	}

	/**
	 * получим флаг влючены ли уведомления
	 */
	public static function getFlagNotificationsEnabled(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["is_notifications_enabled"];
	}

	/**
	 * установим флаг влючен ли звук
	 */
	public static function setFlagSoundEnabled(array $extra, int $is_sound_enabled):array {

		$extra                              = self::_getExtra($extra);
		$extra["extra"]["is_sound_enabled"] = $is_sound_enabled;
		return $extra;
	}

	/**
	 * получим флаг влючен ли звук
	 */
	public static function getFlagSoundEnabled(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["is_sound_enabled"];
	}

	/**
	 * установим флаг может ли приложение работать в фоне
	 */
	public static function setFlagBackgroundWorkEnabled(array $extra, int $is_background_work_enabled):array {

		$extra                                        = self::_getExtra($extra);
		$extra["extra"]["is_background_work_enabled"] = $is_background_work_enabled;
		return $extra;
	}

	/**
	 * получим флаг может ли приложение работать в фоне
	 */
	public static function getFlagBackgroundWorkEnabled(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["is_background_work_enabled"];
	}

	/**
	 * установим размер приложения
	 */
	public static function setSize(array $extra, string $size):array {

		$extra                  = self::_getExtra($extra);
		$extra["extra"]["size"] = $size;
		return $extra;
	}

	/**
	 * получим размер приложения
	 */
	public static function getSize(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["size"];
	}

	/**
	 * установим публичный ключ приложения
	 */
	public static function setPublicKey(array $extra, string $public_key):array {

		$extra                        = self::_getExtra($extra);
		$extra["extra"]["public_key"] = $public_key;
		return $extra;
	}

	/**
	 * получим публичный ключ приложения
	 */
	public static function getPublicKey(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["public_key"];
	}

	/**
	 * установим приватный ключ приложения
	 */
	public static function setPrivateKey(array $extra, string $private_key):array {

		$extra                         = self::_getExtra($extra);
		$extra["extra"]["private_key"] = $private_key;
		return $extra;
	}

	/**
	 * получим приватный ключ приложения
	 */
	public static function getPrivateKey(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["private_key"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * получим актуальную структуру для extra
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