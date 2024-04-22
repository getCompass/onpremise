<?php

namespace Compass\Premise;

/**
 * Класс для взаимодействия с компаниями
 */
class Domain_Company_Entity_Company {

	public const COMPANY_STATUS_CREATING   = 0; // статус компании - создается
	public const COMPANY_STATUS_VACANT     = 1; // статус компании - свободна
	public const COMPANY_STATUS_ACTIVE     = 2; // статус компании - активная
	public const COMPANY_STATUS_HIBERNATED = 10; // компания в гибернации
	public const COMPANY_STATUS_RELOCATING = 40; // компания переезжает
	public const COMPANY_STATUS_DELETED    = 50; // компания удалена
	public const COMPANY_STATUS_INVALID    = 99; // статус компании - недоступна

	// массив для преобразования внутреннего типа системного статуса компании во внешний
	public const SYSTEM_COMPANY_STATUS_SCHEMA = [
		self::COMPANY_STATUS_VACANT     => "vacant",
		self::COMPANY_STATUS_ACTIVE     => "active",
		self::COMPANY_STATUS_HIBERNATED => "hibernated",
		self::COMPANY_STATUS_RELOCATING => "migrating",
		self::COMPANY_STATUS_INVALID    => "invalid",
		self::COMPANY_STATUS_DELETED    => "deleted",
	];

	protected const _EXTRA_VERSION = 5; // версия упаковщика
	protected const _EXTRA_SCHEMA  = [  // схема extra

		5 => [
			"member_count"               => 0,
			"guest_count"                => 0,
			"client_company_id"          => "",
			"latest_public_key_version"  => 1,
			"latest_private_key_version" => 1,

			// публичный ключ (для проверки запросов из компании в pivot)
			"public_key"                 => [
				1 => COMPANY_TO_PIVOT_PUBLIC_KEY,
			],

			// приватный ключ компании (для подписи запросов из pivot в компанию)
			"private_key"                => [
				1 => PIVOT_TO_COMPANY_PRIVATE_KEY,
			],

			// список дополнительных промо для пользователей
			"premium_extra_promo_list"   => [],
		],
	];

	/**
	 * Получаем количество участников в компании
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getMemberCount(array $extra):int {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["member_count"];
	}

	/**
	 * Получаем количество гостей в компании
	 *
	 * @param array $extra
	 *
	 * @return int
	 */
	public static function getGuestCount(array $extra):int {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["guest_count"];
	}

	/**
	 * Получаем client_company_id в extra
	 *
	 * @param array $extra
	 *
	 * @return string
	 */
	public static function getClientCompanyId(array $extra):string {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["client_company_id"];
	}

	/**
	 * Получаем private_key
	 *
	 * @param array $extra
	 *
	 * @return string
	 */
	public static function getPrivateKey(array $extra):string {

		$extra = self::_getExtra($extra);

		return array_pop($extra["extra"]["private_key"]);
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