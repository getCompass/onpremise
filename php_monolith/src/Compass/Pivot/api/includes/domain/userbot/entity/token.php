<?php

namespace Compass\Pivot;

/**
 * Класс для взаимодействия с токеном ботов
 */
class Domain_Userbot_Entity_Token {

	protected const _TOKEN_EXTRA_VERSION = 2; // версия упаковщика
	protected const _TOKEN_EXTRA_SCHEMA  = [  // схема extra

		1 => [
			"secret_key"       => "",
			"is_react_command" => 0,
			"webhook"          => "",
		],

		2 => [
			"secret_key"               => "",
			"is_react_command"         => 0,
			"webhook"                  => "",
			"is_smart_app"             => 0,
			"smart_app_name"           => "",
			"smart_app_url"            => "",
			"is_smart_app_sip"         => 0,
			"is_smart_app_mail"        => 0,
			"smart_app_default_width"  => 414,
			"smart_app_default_height" => 896,
		],
	];

	/**
	 * Создать новую структуру для extra
	 *
	 * @return array
	 */
	public static function initTokenExtra():array {

		return [
			"version" => self::_TOKEN_EXTRA_VERSION,
			"extra"   => self::_TOKEN_EXTRA_SCHEMA[self::_TOKEN_EXTRA_VERSION],
		];
	}

	/**
	 * создаём запись токена
	 *
	 * @throws cs_RowDuplication
	 * @throws \queryException
	 */
	public static function create(string $userbot_id, string $token, string $secret_key,
						int    $is_react_command, string $webhook,
						int    $is_smart_app, string $smart_app_name, string $smart_app_url, int $is_smart_app_sip, int $is_smart_app_mail,
						int    $smart_app_default_width, int $smart_app_default_height,
						int    $created_at):void {

		// добавляем ключи для работы с компанией
		$extra = self::initTokenExtra();

		$extra = self::setSecretKey($extra, $secret_key);
		$extra = self::setFlagReactCommand($extra, $is_react_command);
		$extra = self::setWebhook($extra, $webhook);
		$extra = self::setFlagSmartApp($extra, $is_smart_app);
		$extra = self::setSmartAppName($extra, $smart_app_name);
		$extra = self::setSmartAppUrl($extra, $smart_app_url);
		$extra = self::setFlagSmartAppSip($extra, $is_smart_app_sip);
		$extra = self::setFlagSmartAppMail($extra, $is_smart_app_mail);
		$extra = self::setSmartAppDefaultWidth($extra, $smart_app_default_width);
		$extra = self::setSmartAppDefaultHeight($extra, $smart_app_default_height);

		Gateway_Db_PivotUserbot_TokenList::insert($token, $userbot_id, $created_at, $extra);
	}

	/**
	 * устанавливаем флаг is_react_command
	 */
	public static function setFlagReactCommand(array $extra, int $is_react_command):array {

		$extra                              = self::_getExtra($extra);
		$extra["extra"]["is_react_command"] = $is_react_command == 0 ? 0 : 1;
		return $extra;
	}

	/**
	 * получаем флаг is_react_command
	 */
	public static function getFlagReactCommand(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["is_react_command"] == 0 ? 0 : 1;
	}

	/**
	 * устанавливаем флаг is_smart_app
	 */
	public static function setFlagSmartApp(array $extra, int $is_smart_app):array {

		$extra                          = self::_getExtra($extra);
		$extra["extra"]["is_smart_app"] = $is_smart_app == 0 ? 0 : 1;
		return $extra;
	}

	/**
	 * получаем флаг is_smart_app
	 */
	public static function getFlagSmartApp(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["is_smart_app"] == 0 ? 0 : 1;
	}

	/**
	 * устанавливаем флаг is_smart_app_sip
	 */
	public static function setFlagSmartAppSip(array $extra, int $is_smart_app_sip):array {

		$extra                              = self::_getExtra($extra);
		$extra["extra"]["is_smart_app_sip"] = $is_smart_app_sip == 0 ? 0 : 1;
		return $extra;
	}

	/**
	 * получаем флаг is_smart_app_sip
	 */
	public static function getFlagSmartAppSip(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["is_smart_app_sip"] == 0 ? 0 : 1;
	}

	/**
	 * устанавливаем флаг is_smart_app_mail
	 */
	public static function setFlagSmartAppMail(array $extra, int $is_smart_app_mail):array {

		$extra                               = self::_getExtra($extra);
		$extra["extra"]["is_smart_app_mail"] = $is_smart_app_mail == 0 ? 0 : 1;
		return $extra;
	}

	/**
	 * получаем флаг is_smart_app_mail
	 */
	public static function getFlagSmartAppMail(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["is_smart_app_mail"] == 0 ? 0 : 1;
	}

	/**
	 * устанавливаем дефолтную ширину smart_app
	 */
	public static function setSmartAppDefaultWidth(array $extra, int $smart_app_default_width):array {

		$extra                                     = self::_getExtra($extra);
		$extra["extra"]["smart_app_default_width"] = $smart_app_default_width == 0 ? 0 : 1;
		return $extra;
	}

	/**
	 * получаем дефолтную ширину smart_app
	 */
	public static function getSmartAppDefaultWidth(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["smart_app_default_width"] == 0 ? 0 : 1;
	}

	/**
	 * устанавливаем дефолтную высоту smart_app
	 */
	public static function setSmartAppDefaultHeight(array $extra, int $smart_app_default_Height):array {

		$extra                                      = self::_getExtra($extra);
		$extra["extra"]["smart_app_default_Height"] = $smart_app_default_Height == 0 ? 0 : 1;
		return $extra;
	}

	/**
	 * получаем дефолтную высоту smart_app
	 */
	public static function getSmartAppDefaultHeight(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["smart_app_default_Height"] == 0 ? 0 : 1;
	}

	/**
	 * устанавливаем secret_key
	 */
	public static function setSecretKey(array $extra, string $secret_key):array {

		$extra                        = self::_getExtra($extra);
		$extra["extra"]["secret_key"] = $secret_key;
		return $extra;
	}

	/**
	 * получаем secret_key
	 */
	public static function getSecretKey(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["secret_key"];
	}

	/**
	 * устанавливаем webhook
	 */
	public static function setWebhook(array $extra, string $webhook):array {

		$extra                     = self::_getExtra($extra);
		$extra["extra"]["webhook"] = $webhook;
		return $extra;
	}

	/**
	 * получаем webhook
	 */
	public static function getWebhook(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["webhook"];
	}

	/**
	 * устанавливаем smart_app_name
	 */
	public static function setSmartAppName(array $extra, string $smart_app_name):array {

		$extra                            = self::_getExtra($extra);
		$extra["extra"]["smart_app_name"] = $smart_app_name;
		return $extra;
	}

	/**
	 * получаем smart_app_name
	 */
	public static function getSmartAppName(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["smart_app_name"];
	}

	/**
	 * устанавливаем smart_app_url
	 */
	public static function setSmartAppUrl(array $extra, string $smart_app_url):array {

		$extra                           = self::_getExtra($extra);
		$extra["extra"]["smart_app_url"] = $smart_app_url;
		return $extra;
	}

	/**
	 * получаем smart_app_url
	 */
	public static function getSmartAppUrl(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["smart_app_url"];
	}

	// -------------------------------------------------------
	// UTILS
	// -------------------------------------------------------

	/**
	 * генерируем новый токен
	 *
	 * @throws \Exception
	 */
	public static function generateToken():string {

		return generateUUID();
	}

	/**
	 * генерируем новый ключ шифрования
	 *
	 * @throws \Exception
	 */
	public static function generateSecretKey():string {

		return base64_encode(random_bytes(16));
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
		if ($extra["version"] != self::_TOKEN_EXTRA_VERSION) {

			$extra["extra"]   = array_merge(self::_TOKEN_EXTRA_SCHEMA[self::_TOKEN_EXTRA_VERSION], $extra["extra"]);
			$extra["version"] = self::_TOKEN_EXTRA_VERSION;
		}

		return $extra;
	}
}
