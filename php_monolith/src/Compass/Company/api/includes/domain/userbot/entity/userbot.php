<?php

namespace Compass\Company;

/**
 * Класс для взаимодействия с ботами
 */
class Domain_Userbot_Entity_Userbot {

	// список статусов бота
	public const STATUS_DISABLE = 0; // бот неактивен
	public const STATUS_ENABLE  = 1; // бот активен
	public const STATUS_DELETE  = 2; // бот удалён

	// список цветов бота
	public const AVATAR_COLOR_IVORY_ID      = 1; // цвет слоновой кости
	public const AVATAR_COLOR_HONEYDEW_ID   = 2; // цвет нектара
	public const AVATAR_COLOR_LIGHT_BLUE_ID = 3; // светло-синий цвет
	public const AVATAR_COLOR_MINT_ID       = 4; // мятный цвет
	public const AVATAR_COLOR_LAVENDER_ID   = 5; // цвет лаванды
	public const AVATAR_COLOR_CUPCAKE_ID    = 6; // цвет кекса

	// доступные цвета для установки пользовательскому боту
	public const ALLOWED_AVATAR_COLOR_ID = [
		self::AVATAR_COLOR_IVORY_ID,
		self::AVATAR_COLOR_HONEYDEW_ID,
		self::AVATAR_COLOR_LIGHT_BLUE_ID,
		self::AVATAR_COLOR_MINT_ID,
		self::AVATAR_COLOR_LAVENDER_ID,
		self::AVATAR_COLOR_CUPCAKE_ID,
	];

	/**
	 * создание записи с ботом
	 *
	 * @throws \queryException
	 */
	public static function create(string $userbot_id, int $userbot_user_id, int $status,
						int    $is_react_command, string $webhook,
						string $token, string $secret_key, int $avatar_color_id, string $avatar_file_key):Struct_Db_CloudCompany_Userbot {

		$extra = self::initExtra();

		$extra = self::setToken($extra, $token);
		$extra = self::setSecretKey($extra, $secret_key);
		$extra = self::setWebhook($extra, $webhook);
		$extra = self::setAvatarColorId($extra, $avatar_color_id);
		$extra = self::setAvatarFileKey($extra, $avatar_file_key);
		$extra = self::setFlagReactCommand($extra, $is_react_command);

		$created_at = time();

		Gateway_Db_CompanyData_UserbotList::insert($userbot_id, $userbot_user_id, $status, $created_at, $extra);

		return new Struct_Db_CloudCompany_Userbot(
			$userbot_id,
			$status,
			$userbot_user_id,
			$created_at,
			0,
			$extra
		);
	}

	/**
	 * Получаем случайный цвет аватара из доступных
	 *
	 * @return int
	 */
	public static function getRandomAvatarColorId():int {

		$max_count = count(self::ALLOWED_AVATAR_COLOR_ID) - 1;
		return self::ALLOWED_AVATAR_COLOR_ID[random_int(0, $max_count)];
	}

	/**
	 * получим запись с пользовательским ботом
	 *
	 * @throws Domain_Userbot_Exception_UserbotNotFound
	 */
	public static function get(string $userbot_id):Struct_Db_CloudCompany_Userbot {

		return Gateway_Db_CompanyData_UserbotList::getOne($userbot_id);
	}

	/**
	 * включаем бота
	 *
	 * @throws \parseException
	 */
	public static function enable(Struct_Db_CloudCompany_Userbot $userbot):void {

		Gateway_Db_CompanyData_UserbotList::set($userbot->userbot_id, [
			"status_alias" => self::STATUS_ENABLE,
			"updated_at"   => time(),
			"extra"        => $userbot->extra,
		]);
	}

	/**
	 * отключаем бота
	 *
	 * @throws \parseException
	 */
	public static function disable(Struct_Db_CloudCompany_Userbot $userbot):void {

		Gateway_Db_CompanyData_UserbotList::set($userbot->userbot_id, [
			"status_alias" => self::STATUS_DISABLE,
			"updated_at"   => time(),
			"extra"        => $userbot->extra,
		]);
	}

	/**
	 * удаляем бота
	 *
	 * @throws \parseException
	 */
	public static function delete(Struct_Db_CloudCompany_Userbot $userbot):void {

		Gateway_Db_CompanyData_UserbotList::set($userbot->userbot_id, [
			"status_alias" => self::STATUS_DELETE,
			"updated_at"   => time(),
			"extra"        => $userbot->extra,
		]);
	}

	/**
	 * текст сообщения имеет формат команды?
	 */
	public static function isFormatCommand(string $message_text):bool {

		return mb_substr($message_text, 0, 1) == "/";
	}

	// -------------------------------------------------------
	// EXTRA SCHEMA
	// -------------------------------------------------------

	protected const _EXTRA_VERSION = 3; // версия упаковщика
	protected const _EXTRA_SCHEMA  = [  // схема extra

		1 => [
			"token"            => "",
			"secret_key"       => "",
			"is_react_command" => 0,
			"webhook"          => "",
			"command_list"     => [],
			"disabled_at"      => 0,
			"deleted_at"       => 0,
			"avatar_color_id"  => 0,
		],

		2 => [
			"token"                    => "",
			"secret_key"               => "",
			"is_react_command"         => 0,
			"webhook"                  => "",
			"is_smart_app"             => 0,
			"smart_app_url"            => "",
			"is_smart_app_sip"         => 0,
			"is_smart_app_mail"        => 0,
			"smart_app_default_width"  => 414,
			"smart_app_default_height" => 896,
			"command_list"             => [],
			"disabled_at"              => 0,
			"deleted_at"               => 0,
			"avatar_color_id"          => 0,
			"avatar_file_key"          => "",
		],

		3 => [
			"token"                    => "",
			"secret_key"               => "",
			"is_react_command"         => 0,
			"webhook"                  => "",
			"is_smart_app"             => 0,
			"smart_app_url"            => "",
			"is_smart_app_sip"         => 0,
			"is_smart_app_mail"        => 0,
			"smart_app_default_width"  => 414,
			"smart_app_default_height" => 896,
			"command_list"             => [],
			"disabled_at"              => 0,
			"deleted_at"               => 0,
			"avatar_color_id"          => 0,
			"avatar_file_key"          => "",
			"smart_app_public_key"     => "",
			"smart_app_private_key"    => "",
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
	 * установим вебхук
	 */
	public static function setWebhook(array $extra, string $token):array {

		$extra                     = self::_getExtra($extra);
		$extra["extra"]["webhook"] = $token;
		return $extra;
	}

	/**
	 * установим токен бота
	 */
	public static function setToken(array $extra, string $token):array {

		$extra                   = self::_getExtra($extra);
		$extra["extra"]["token"] = $token;
		return $extra;
	}

	/**
	 * установим секретный ключ бота
	 */
	public static function setSecretKey(array $extra, string $secret_key):array {

		$extra                        = self::_getExtra($extra);
		$extra["extra"]["secret_key"] = $secret_key;
		return $extra;
	}

	/**
	 * установим список команд
	 */
	public static function setCommandList(array $extra, array $command_list):array {

		$extra                          = self::_getExtra($extra);
		$extra["extra"]["command_list"] = $command_list;
		return $extra;
	}

	/**
	 * установим идентификатор аватарки бота
	 */
	public static function setAvatarColorId(array $extra, int $avatar_color_id):array {

		$extra                             = self::_getExtra($extra);
		$extra["extra"]["avatar_color_id"] = $avatar_color_id;
		return $extra;
	}

	/**
	 * установим file_key аватарки бота
	 */
	public static function setAvatarFileKey(array $extra, string $avatar_file_key):array {

		$extra                             = self::_getExtra($extra);
		$extra["extra"]["avatar_file_key"] = $avatar_file_key;
		return $extra;
	}

	/**
	 * установим время когда отключили бота
	 */
	public static function setDisabledAt(array $extra, int $disabled_at):array {

		$extra                         = self::_getExtra($extra);
		$extra["extra"]["disabled_at"] = $disabled_at;
		return $extra;
	}

	/**
	 * установим время когда удалили бота
	 */
	public static function setDeletedAt(array $extra, int $deleted_at):array {

		$extra                        = self::_getExtra($extra);
		$extra["extra"]["deleted_at"] = $deleted_at;
		return $extra;
	}

	/**
	 * установим флаг реагирует ли бот на команды
	 */
	public static function setFlagReactCommand(array $extra, int $is_react_command):array {

		$extra                              = self::_getExtra($extra);
		$extra["extra"]["is_react_command"] = $is_react_command;
		return $extra;
	}

	/**
	 * получим вебхук бота
	 */
	public static function getWebhook(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["webhook"];
	}

	/**
	 * получим токен бота
	 */
	public static function getToken(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["token"];
	}

	/**
	 * получим секретный ключ бота
	 */
	public static function getSecretKey(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["secret_key"];
	}

	/**
	 * получим список команд
	 */
	public static function getCommandList(array $extra):array {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["command_list"];
	}

	/**
	 * получим идентификатор аватарки бота
	 */
	public static function getAvatarColorId(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["avatar_color_id"];
	}

	/**
	 * получим file_key аватарки бота
	 */
	public static function getAvatarFileKey(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["avatar_file_key"];
	}

	/**
	 * получим временную метку отключения бота
	 */
	public static function getDisabledAt(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["disabled_at"];
	}

	/**
	 * получим временную метку удаления бота
	 */
	public static function getDeletedAt(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["deleted_at"];
	}

	/**
	 * получим флаг реагирует ли бот на команды
	 */
	public static function getFlagReactCommand(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["is_react_command"];
	}

	/**
	 * реагирует ли бот на команды
	 */
	public static function isReactCommand(array $extra):bool {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["is_react_command"] == 1;
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
