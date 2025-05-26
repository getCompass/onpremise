<?php

namespace Compass\Pivot;

/**
 * Класс для взаимодействия с ботами
 */
class Domain_Userbot_Entity_Userbot {

	public const STATUS_DISABLE = 0; // бот неактивен
	public const STATUS_ENABLE  = 1; // бот активен
	public const STATUS_DELETE  = 2; // бот удален

	/**
	 * @var int последняя актуальная версия webhook бота
	 * !!! ВНИМАНИЕ !!! Версия webhook также указана в модуле php_userbot
	 */
	public const LAST_WEBHOOK_VERSION = 3;

	// !!! структура schema и версия webhook также продублирована в php_userbot
	protected const _USERBOT_EXTRA_VERSION = 3; // версия упаковщика
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

		3 => [
			"command_list"    => [],
			"avatar_color_id" => 0,
			"avatar_file_key" => "",
			"webhook_version" => 1, // по умолчанию считаем, что у бота 1я версия, чтобы не сломать текущих ботов
		],
	];

	// url для api-документатор бота
	public const API_DOCUMENTATION_URL = "https://github.com/getCompass/userbot";

	/**
	 * Получаем дефолтную аватарку для пользовательского бота
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getDefaultUserbotAvatar(int $avatar_id):string {

		$default_file = Gateway_Db_PivotSystem_DefaultFileList::get("userbot_avatar_{$avatar_id}");

		return $default_file->file_key;
	}

	/**
	 * Получаем аватарку для неактивного пользовательского бота
	 *
	 * @throws \cs_RowIsEmpty
	 */
	public static function getInactiveUserbotAvatar():string {

		$default_file = Gateway_Db_PivotSystem_DefaultFileList::get("userbot_avatar_sleep");

		return $default_file->file_key;
	}

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
	 * Создание записи с ботом
	 *
	 * @throws cs_RowDuplication
	 * @throws \queryException
	 */
	public static function create(string $userbot_id, int $user_id, int $company_id, int $avatar_color_id, string $avatar_file_key, int $created_at):void {

		$extra = self::initUserbotExtra();
		$extra = self::setAvatarColorId($extra, $avatar_color_id);
		$extra = self::setAvatarFileKey($extra, $avatar_file_key);
		$extra = self::setWebhookVersion($extra, self::LAST_WEBHOOK_VERSION);

		Gateway_Db_PivotUserbot_UserbotList::insert($userbot_id, $user_id, $company_id, self::STATUS_ENABLE, $created_at, $extra);
	}

	/**
	 * Обновляем статус записи с ботом
	 */
	public static function changeStatus(string $userbot_id, int $status):void {

		$set = [
			"status"     => $status,
			"updated_at" => time(),
		];
		Gateway_Db_PivotUserbot_UserbotList::set($userbot_id, $set);
	}

	/**
	 * устанавливаем список команд бота
	 */
	public static function setCommandList(array $extra, array $command_list):array {

		$extra                          = self::_getExtra($extra);
		$extra["extra"]["command_list"] = $command_list;
		return $extra;
	}

	/**
	 * устанавливаем avatar_color_id
	 */
	public static function setAvatarColorId(array $extra, int $avatar_color_id):array {

		$extra                             = self::_getExtra($extra);
		$extra["extra"]["avatar_color_id"] = $avatar_color_id;
		return $extra;
	}

	/**
	 * достаём avatar_color_id
	 */
	public static function getAvatarColorId(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["avatar_color_id"];
	}

	/**
	 * устанавливаем avatar_file_key
	 */
	public static function setAvatarFileKey(array $extra, string $avatar_file_key):array {

		$extra                             = self::_getExtra($extra);
		$extra["extra"]["avatar_file_key"] = $avatar_file_key;
		return $extra;
	}

	/**
	 * достаём avatar_file_key
	 */
	public static function getAvatarFileKey(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["avatar_file_key"];
	}

	/**
	 * получаем версию webhook бота из extra
	 */
	public static function getWebhookVersion(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["webhook_version"];
	}

	/**
	 * устанавливаем версию webhook бота в extra
	 */
	public static function setWebhookVersion(array $extra, int $version):array {

		$extra                             = self::_getExtra($extra);
		$extra["extra"]["webhook_version"] = $version;
		return $extra;
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	/**
	 * генерируем новый userbot_id
	 *
	 * @throws \Exception
	 */
	public static function generateUserbotId():string {

		return bin2hex(random_bytes(16));
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получить актуальную структуру для extra
	 *
	 */
	protected static function _getExtra(array $extra):array {

		// если версия не совпадает - дополняем её до текущей
		if ($extra["version"] != self::_USERBOT_EXTRA_VERSION) {

			$extra["extra"]   = array_merge(self::_USERBOT_EXTRA_SCHEMA[self::_USERBOT_EXTRA_VERSION], $extra["extra"]);
			$extra["version"] = self::_USERBOT_EXTRA_VERSION;
		}

		return $extra;
	}
}
