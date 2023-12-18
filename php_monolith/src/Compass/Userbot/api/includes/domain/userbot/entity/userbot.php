<?php

namespace Compass\Userbot;

/**
 * Класс для взаимодействия с сущностью бота
 */
class Domain_Userbot_Entity_Userbot {

	// список версий webhook бота
	// !!! ВНИМАНИЕ !!! Версия webhook также указана в модуле php_pivot
	public const USERBOT_WEBHOOK_VERSION_1 = 1;
	public const USERBOT_WEBHOOK_VERSION_2 = 2;
	public const USERBOT_WEBHOOK_VERSION_3 = 3;

	// список статусов бота
	public const STATUS_DISABLE = 0; // бот неактивен
	public const STATUS_ENABLE  = 1; // бот активен
	public const STATUS_DELETE  = 2; // бот удалён

	/**
	 * проверяем, что бот включён
	 *
	 * @throws \cs_Userbot_IsNotEnabled
	 */
	public static function assertUserbotEnabled(Struct_Userbot_Info $userbot):void {

		if ($userbot->status != Domain_Userbot_Entity_Userbot::STATUS_ENABLE) {
			throw new \cs_Userbot_IsNotEnabled();
		}
	}

	// -------------------------------------------------------
	// EXTRA SCHEMA
	// -------------------------------------------------------

	// !!! структура schema и версия webhook также продублирована в php_pivot
	protected const _USERBOT_EXTRA_VERSION = 2; // версия упаковщика
	protected const _USERBOT_EXTRA_SCHEMA  = [  // схема extra

		1 => [
			"command_list"    => [],
			"avatar_color_id" => 0,
		],
		2 => [
			"command_list"    => [],
			"avatar_color_id" => 0,
			"webhook_version" => 1, // по умолчанию считаем, что у бота 1я версия, чтобы не сломать текущих ботов
		],
	];

	/**
	 * Создать новую структуру для extra
	 *
	 * @return array
	 */
	public static function initUserbotExtra():array {

		return [
			"version" => self::_USERBOT_EXTRA_VERSION,
			"extra"   => self::_USERBOT_EXTRA_SCHEMA[self::_USERBOT_EXTRA_VERSION],
		];
	}

	/**
	 * получаем версию webhook бота из extra
	 */
	public static function getWebhookVersion(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["webhook_version"];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получить актуальную структуру для extra
	 *
	 */
	protected static function _getExtra(array $extra):array {

		// на случае если из кэша получили бота, у которого отсутствует extra
		if (!isset($extra["version"])) {
			return self::initUserbotExtra();
		}

		// если версия не совпадает - дополняем её до текущей
		if ($extra["version"] != self::_USERBOT_EXTRA_VERSION) {

			$extra["extra"]   = array_merge(self::_USERBOT_EXTRA_SCHEMA[self::_USERBOT_EXTRA_VERSION], $extra["extra"]);
			$extra["version"] = self::_USERBOT_EXTRA_VERSION;
		}

		return $extra;
	}
}