<?php

namespace Compass\Company;

use BaseFrame\Exception\Gateway\DBShardingNotFoundException;
use BaseFrame\Exception\Gateway\QueryFatalException;

/**
 * Класс для взаимодействия с пользовательскими настройками smart app
 */
class Domain_SmartApp_Entity_SmartAppUserRel {

	// список статусов приложения
	public const STATUS_ENABLE = 1; // приложение активно
	public const STATUS_DELETE = 2; // приложение удалено

	/**
	 * создание записи с настройками пользователя в приложении
	 *
	 * @param int    $smart_app_id
	 * @param int    $user_id
	 * @param int    $status
	 * @param string $title
	 * @param string $avatar_file_key
	 * @param int    $is_default_avatar
	 * @param int    $is_open_in_new_window
	 * @param int    $is_notifications_enabled
	 * @param int    $is_sound_enabled
	 * @param int    $is_background_work_enabled
	 * @param string $size
	 * @param int    $created_at
	 *
	 * @return Struct_Db_CompanyData_SmartAppUserRel
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 * @long
	 */
	public static function create(int    $smart_app_id, int $user_id, int $status, string $title, string $avatar_file_key, int $is_default_avatar,
						int    $is_open_in_new_window, int $is_notifications_enabled, int $is_sound_enabled, int $is_background_work_enabled,
						string $size, int $created_at):Struct_Db_CompanyData_SmartAppUserRel {

		$extra = self::initExtra();

		$extra      = self::setTitle($extra, $title);
		$extra      = self::setAvatarFileKey($extra, $avatar_file_key);
		$extra      = self::setFlagDefaultAvatar($extra, $is_default_avatar);
		$extra      = self::setFlagOpenInNewWindow($extra, $is_open_in_new_window);
		$extra      = self::setFlagNotificationsEnabled($extra, $is_notifications_enabled);
		$extra      = self::setFlagSoundEnabled($extra, $is_sound_enabled);
		$extra      = self::setFlagBackgroundWorkEnabled($extra, $is_background_work_enabled);
		$extra      = self::setSize($extra, $size);
		$deleted_at = 0;

		try {

			$smart_app_user_rel = Gateway_Db_CompanyData_SmartAppUserRel::getOne($smart_app_id, $user_id);
			if ($smart_app_user_rel->status !== Domain_SmartApp_Entity_SmartAppUserRel::STATUS_ENABLE) {

				Gateway_Db_CompanyData_SmartAppUserRel::set($smart_app_id, $user_id, [
					"status"     => Domain_SmartApp_Entity_SmartAppUserRel::STATUS_ENABLE,
					"deleted_at" => $deleted_at,
					"created_at" => $created_at,
					"updated_at" => 0,
					"extra"      => $extra,
				]);
			}
		} catch (Domain_SmartApp_Exception_SmartAppNotFound) {
			Gateway_Db_CompanyData_SmartAppUserRel::insert($smart_app_id, $user_id, $status, $deleted_at, $created_at, $extra);
		}

		return new Struct_Db_CompanyData_SmartAppUserRel(
			$smart_app_id,
			$user_id,
			$status,
			$deleted_at,
			$created_at,
			0,
			$extra
		);
	}

	/**
	 * удаляем приложение
	 *
	 * @param Struct_Db_CompanyData_SmartAppUserRel $smart_app_user_rel
	 *
	 * @throws DBShardingNotFoundException
	 * @throws QueryFatalException
	 */
	public static function delete(Struct_Db_CompanyData_SmartAppUserRel $smart_app_user_rel):void {

		Gateway_Db_CompanyData_SmartAppUserRel::set($smart_app_user_rel->smart_app_id, $smart_app_user_rel->user_id, [
			"status"     => self::STATUS_DELETE,
			"deleted_at" => $smart_app_user_rel->deleted_at,
			"updated_at" => time(),
			"extra"      => $smart_app_user_rel->extra,
		]);
	}

	// -------------------------------------------------------
	// EXTRA SCHEMA
	// -------------------------------------------------------

	protected const _EXTRA_VERSION = 2; // версия упаковщика
	protected const _EXTRA_SCHEMA  = [  // схема extra

		1 => [
			"title"                      => "",
			"avatar_file_key"            => "",
			"is_open_in_new_window"      => 0,
			"is_notifications_enabled"   => 0,
			"is_sound_enabled"           => 0,
			"is_background_work_enabled" => 0,
			"size"                       => "",
		],

		2 => [
			"title"                      => "",
			"avatar_file_key"            => "",
			"is_default_avatar"          => 1,
			"is_open_in_new_window"      => 0,
			"is_notifications_enabled"   => 0,
			"is_sound_enabled"           => 0,
			"is_background_work_enabled" => 0,
			"size"                       => "",
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
	 * установим флаг дефолтный ли аватар установлен
	 */
	public static function setFlagDefaultAvatar(array $extra, int $is_default_avatar):array {

		$extra                               = self::_getExtra($extra);
		$extra["extra"]["is_default_avatar"] = $is_default_avatar;
		return $extra;
	}

	/**
	 * получим флаг дефолтный ли аватар установлен
	 */
	public static function getFlagDefaultAvatar(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["is_default_avatar"];
	}

	/**
	 * установим флаг открывать ли приложение в новом окне
	 */
	public static function setFlagOpenInNewWindow(array $extra, int $is_open_in_new_window):array {

		$extra                                   = self::_getExtra($extra);
		$extra["extra"]["is_open_in_new_window"] = $is_open_in_new_window;
		return $extra;
	}

	/**
	 * получим флаг открывать ли приложение в новом окне
	 */
	public static function getFlagOpenInNewWindow(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["is_open_in_new_window"];
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