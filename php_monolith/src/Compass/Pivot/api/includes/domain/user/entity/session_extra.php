<?php

namespace Compass\Pivot;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Server\ServerProvider;

/**
 * Класс для работы с экстра-данными сессии пользователя.
 */
class Domain_User_Entity_SessionExtra {

	public const SAAS_SMS_LOGIN_TYPE         = 1;   // тип авторизации на saas: sms
	public const SAAS_QRCODE_LOGIN_TYPE      = 2;   // тип авторизации на saas: qr-код
	public const ONPREMISE_SMS_LOGIN_TYPE    = 100; // тип авторизации на onpremise: sms
	public const ONPREMISE_EMAIL_LOGIN_TYPE  = 101; // тип авторизации на onpremise: email
	public const ONPREMISE_LDAP_LOGIN_TYPE   = 102; // тип авторизации на onpremise: ldap
	public const ONPREMISE_SSO_LOGIN_TYPE    = 103; // тип авторизации на onpremise: sso
	public const ONPREMISE_QRCODE_LOGIN_TYPE = 104; // тип авторизации на onpremise: qr-код

	// тип авторизации на onpremise на сайте
	// отдельный тип сессии, когда авторизуем пользователя на сайте onpremise по коду, email, ldap, etc
	// но эта тип авторизации этой сессии не считается за авторизацию через код, email, ldap, etc
	public const ONPREMISE_WEB_LOGIN_TYPE    = 200;

	protected const _SAAS_LOGIN_TYPE_LIST = [
		self::SAAS_SMS_LOGIN_TYPE,
		self::SAAS_QRCODE_LOGIN_TYPE,
	];

	protected const _ONPREMISE_LOGIN_TYPE_LIST = [
		self::ONPREMISE_SMS_LOGIN_TYPE,
		self::ONPREMISE_EMAIL_LOGIN_TYPE,
		self::ONPREMISE_LDAP_LOGIN_TYPE,
		self::ONPREMISE_SSO_LOGIN_TYPE,
		self::ONPREMISE_QRCODE_LOGIN_TYPE,
		self::ONPREMISE_WEB_LOGIN_TYPE,
	];

	public const LOGOUT_INVALIDATE_REASON         = 1;
	public const LOGOUT_DEVICE_INVALIDATE_REASON  = 2;
	public const DELETE_ACCOUNT_INVALIDATE_REASON = 10;
	public const CHANGE_PHONE_INVALIDATE_REASON   = 11;

	/** Типы устройств пользователя */
	protected const _MOBILE_DEVICE_TYPE         = 1; // mobile
	protected const _ELECTRON_DEVICE_TYPE       = 2; // desktop
	protected const _OPEN_API_AGENT_DEVICE_TYPE = 3; // open API agent

	/** Форматирование типа устройства пользователя */
	protected const _DEVICE_TYPE_OUTPUT_SCHEMA = [
		self::_MOBILE_DEVICE_TYPE         => "mobile",
		self::_ELECTRON_DEVICE_TYPE       => "desktop",
		self::_OPEN_API_AGENT_DEVICE_TYPE => "open_api",
	];

	// версия упаковщика
	protected const _EXTRA_VERSION = 1;

	// схема extra по версиям
	protected const _EXTRA_SCHEMA = [
		1 => [
			"login_type"        => 0,
			"device_id"         => "",
			"device_type"       => 0,
			"device_name"       => "",
			"app_version"       => "",
			"server_version"    => "",
			"invalidate_reason" => 0,
		],
	];

	/**
	 * Создать новую структуру для extra.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	public static function initExtra():array {

		return [
			"version" => static::_EXTRA_VERSION,
			"extra"   => static::_EXTRA_SCHEMA[static::_EXTRA_VERSION],
		];
	}

	// -------------------------------------------------------
	// SET METHODS
	// -------------------------------------------------------

	/**
	 * Устанавливает login_type для сессии устройства.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	public static function setLoginType(array $extra, int $login_type):array {

		if ($login_type != 0 && ServerProvider::isSaas() && !in_array($login_type, self::_SAAS_LOGIN_TYPE_LIST)) {
			throw new ParseFatalException("incorrect login_type = {$login_type} on saas");
		}

		if ($login_type != 0 && ServerProvider::isOnPremise() && !in_array($login_type, self::_ONPREMISE_LOGIN_TYPE_LIST)) {
			throw new ParseFatalException("incorrect login_type = {$login_type} on onpremise");
		}

		$extra = self::_getExtra($extra);

		$extra["extra"]["login_type"] = $login_type;
		return $extra;
	}

	/**
	 * Устанавливает device_id устройства пользователя.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	public static function setDeviceId(array $extra, string $device_id):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["device_id"] = $device_id;
		return $extra;
	}

	/**
	 * Устанавливает тип устройства пользователя.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	public static function setOutputDeviceType(array $extra, string $device_type):array {

		$flipped_output_schema = array_flip(self::_DEVICE_TYPE_OUTPUT_SCHEMA);

		if (!isset($flipped_output_schema[$device_type])) {
			return self::setDeviceType($extra, 0);
		}

		return self::setDeviceType($extra, $flipped_output_schema[$device_type]);
	}

	/**
	 * Устанавливает тип устройства пользователя.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	public static function setDeviceType(array $extra, int $device_type):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["device_type"] = $device_type;
		return $extra;
	}

	/**
	 * Устанавливает имя устройства пользователя.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	public static function setDeviceName(array $extra, string $device_name):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["device_name"] = $device_name;
		return $extra;
	}

	/**
	 * Устанавливает версию клиентского приложения.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	public static function setAppVersion(array $extra, string $app_version):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["app_version"] = $app_version;
		return $extra;
	}

	/**
	 * Устанавливает версию сервера.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	public static function setServerVersion(array $extra, string $server_version):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["server_version"] = $server_version;
		return $extra;
	}

	/**
	 * Устанавливает причину инвалидации сессии.
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	public static function setInvalidateReason(array $extra, int $invalidate_reason):array {

		$extra = self::_getExtra($extra);

		$extra["extra"]["invalidate_reason"] = $invalidate_reason;
		return $extra;
	}

	// -------------------------------------------------------
	// GET METHODS
	// -------------------------------------------------------

	/**
	 * Получаем device_id устройства пользователя.
	 */
	public static function getDeviceId(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["device_id"];
	}

	/**
	 * Получаем device_id устройства пользователя.
	 */
	public static function getDeviceType(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["device_type"];
	}

	/**
	 * Получаем имя модели устройства пользователя.
	 */
	public static function getDeviceName(array $extra):string {

		$extra       = self::_getExtra($extra);
		$device_name = $extra["extra"]["device_name"];

		return mb_strlen($device_name) < 1 ? "Устройство" : $device_name;
	}

	/**
	 * Получаем тип авторизации.
	 */
	public static function getLoginType(array $extra):int {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["login_type"];
	}

	/**
	 * Получаем версию приложения на момент авторизации.
	 */
	public static function getAppVersion(array $extra):string {

		$extra       = self::_getExtra($extra);
		$app_version = $extra["extra"]["app_version"];

		return mb_strlen($app_version) < 1 ? "-" : $app_version;
	}

	/**
	 * Получаем версию сервера на момент авторизации.
	 */
	public static function getServerVersion(array $extra):string {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["server_version"];
	}

	/**
	 * Получаем device_id устройства пользователя.
	 */
	public static function getOutputDeviceType(array $extra):string {

		return self::_DEVICE_TYPE_OUTPUT_SCHEMA[self::getDeviceType($extra)] ?? "unknown";
	}

	/**
	 * Получить login_type из auth_story авторизации
	 */
	public static function getLoginTypeByAuthType(int $auth_type):int {

		if (Domain_User_Entity_AuthStory::isPhoneNumberAuth($auth_type)) {
			return ServerProvider::isSaas() ? self::SAAS_SMS_LOGIN_TYPE : self::ONPREMISE_SMS_LOGIN_TYPE;
		}
		if (Domain_User_Entity_AuthStory::isMailAuth($auth_type)
			|| Domain_User_Entity_AuthStory::isMailResetPassword($auth_type)) {
			return self::ONPREMISE_EMAIL_LOGIN_TYPE;
		}
		if (Domain_User_Entity_AuthStory::isSsoAuth($auth_type)) {
			return self::ONPREMISE_SSO_LOGIN_TYPE;
		}
		if (Domain_User_Entity_AuthStory::isLdapAuth($auth_type)) {
			return self::ONPREMISE_LDAP_LOGIN_TYPE;
		}

		return 0;
	}

	# region protected

	/**
	 * Получить актуальную структуру для extra
	 */
	#[\JetBrains\PhpStorm\ArrayShape(["version" => "int", "extra" => "array"])]
	protected static function _getExtra(array $extra):array {

		// если экстра была пустая
		if (!isset($extra["version"])) {

			$extra["extra"]   = array_merge(static::_EXTRA_SCHEMA[static::_EXTRA_VERSION], []);
			$extra["version"] = static::_EXTRA_VERSION;
		}

		// если версия не совпадает - дополняем её до текущей
		if ((int) $extra["version"] !== static::_EXTRA_VERSION) {

			$extra["extra"]   = array_merge(static::_EXTRA_SCHEMA[static::_EXTRA_VERSION], $extra["extra"]);
			$extra["version"] = static::_EXTRA_VERSION;
		}

		return $extra;
	}

	# endregion protected
}
