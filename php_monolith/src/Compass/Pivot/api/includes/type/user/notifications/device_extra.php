<?php

namespace Compass\Pivot;

/**
 * Class Type_User_Notifications_DeviceExtra
 */
class Type_User_Notifications_DeviceExtra {

	// текущая версия extra
	protected const _EXTRA_DEVICE_VERSION = 2;

	protected const _EXTRA_DEVICE_SCHEMA = [

		1 => [
			"token_list"                   => [],
			"user_company_push_token_list" => [],
		],

		2 => [
			"token_list"                   => [],
			"user_company_push_token_list" => [],
			"company_id_list"              => [],
		],
	];

	/**
	 * возвращает текущую структуру extra device_list с default значениями
	 *
	 */
	public static function init():array {

		return [
			"handler_version" => self::_EXTRA_DEVICE_VERSION,
			"extra"           => self::_EXTRA_DEVICE_SCHEMA[self::_EXTRA_DEVICE_VERSION],
		];
	}

	/**
	 * Получаем список токенов
	 *
	 */
	public static function getTokenList(array $extra):array {

		// получаем актуальное extra
		$extra = self::_get($extra);

		return $extra["extra"]["token_list"];
	}

	/**
	 * Изменить список токенов для девайса
	 *
	 */
	public static function setTokenList(array $extra, array $token_list):array {

		// получаем актуальное extra
		$extra = self::_get($extra);

		$extra["extra"]["token_list"] = $token_list;

		return $extra;
	}

	/**
	 * получить токен для пушей компании
	 *
	 */
	public static function getUserCompanyPushTokenList(array $extra):array {

		// получаем актуальное extra
		$extra = self::_get($extra);

		return $extra["extra"]["user_company_push_token_list"];
	}

	/**
	 * получить список компаний для девайса
	 *
	 */
	public static function getCompanyIdList(array $extra):array {

		// получаем актуальное extra
		$extra = self::_get($extra);

		return $extra["extra"]["company_id_list"];
	}

	/**
	 * Изменить список компанейских токенов для девайса
	 *
	 */
	public static function setUserCompanyPushTokenList(array $extra, array $user_company_push_token_list):array {

		// получаем актуальное extra
		$extra = self::_get($extra);

		$extra["extra"]["user_company_push_token_list"] = $user_company_push_token_list;

		return $extra;
	}

	/**
	 * Изменить список компаний для девайса
	 *
	 */
	public static function setCompanyIdList(array $extra, array $company_id_list):array {

		// получаем актуальное extra
		$extra = self::_get($extra);

		$extra["extra"]["company_id_list"] = $company_id_list;

		return $extra;
	}

	/**
	 * получить extra девайса
	 *
	 */
	protected static function _get(array $extra):array {

		// сравниваем версию пришедшей extra с текущей
		if ($extra["handler_version"] != self::_EXTRA_DEVICE_VERSION) {

			// сливаем текущую версию extra и ту, что пришла
			$extra["extra"]           = array_merge(self::_EXTRA_DEVICE_SCHEMA[self::_EXTRA_DEVICE_VERSION], $extra["extra"]);
			$extra["handler_version"] = self::_EXTRA_DEVICE_VERSION;
		}

		return $extra;
	}
}
