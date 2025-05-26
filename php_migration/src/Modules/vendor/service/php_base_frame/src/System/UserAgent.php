<?php

namespace BaseFrame\System;

use BaseFrame\Exception\Request\AppNameNotFoundException;
use BaseFrame\Exception\Request\PlatformNotFoundException;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Функции для работы с user agent клиента, выполняющего запрос
 */
class UserAgent {

	public const USER_AGENT_ROBOT = "robot";

	// константа для неизвестной версии приложения у пользователя
	public const APP_VERSION_UNKNOWN = "unknown";

	// текущие возможные платформы
	public const PLATFORM_ELECTRON = "electron";
	public const PLATFORM_ANDROID  = "android";
	public const PLATFORM_IOS      = "iphone";
	public const PLATFORM_IPAD     = "ipad";
	public const PLATFORM_OTHER    = "other";

	public const COMPASS_APP = "compass";
	public const COMTEAM_APP = "comteam";

	// доступные платформы
	protected const _AVAILABLE_PLATFORM_LIST = [
		self::PLATFORM_ELECTRON,
		self::PLATFORM_IOS,
		self::PLATFORM_ANDROID,
		self::PLATFORM_IPAD,
	];

	// поддерживаемые типы приложения
	protected const _AVAILABLE_APP_NAME_LIST = [
		self::COMPASS_APP,
		self::COMTEAM_APP,
	];

	/**
	 * Возвращает user agent пользователя
	 *
	 * @return string
	 */
	public static function getUserAgent():string {

		if (!isset($_SERVER["HTTP_USER_AGENT"]) || $_SERVER["HTTP_USER_AGENT"] == "") {
			$_SERVER["HTTP_USER_AGENT"] = self::USER_AGENT_ROBOT;
		}

		return formatString($_SERVER["HTTP_USER_AGENT"]);
	}

	/**
	 * Получить платформу клиента
	 *
	 * @param string|null $user_agent
	 *
	 * @return string
	 */
	public static function getPlatform(string $user_agent = null):string {

		// если работаем из консоли и не передали ua - возвращаем платформу other
		if (isCLi() && is_null($user_agent)) {
			return self::PLATFORM_OTHER;
		}

		if (is_null($user_agent)) {
			$user_agent = self::getUserAgent();
		}

		$user_agent = mb_strtolower($user_agent);

		// ищем платформу в списке доступных
		$platform = self::_getPlatformFromList($user_agent);

		// если не нашли платформу, значит платформа неизвестна для нас
		if (strlen($platform) < 1) {
			return self::PLATFORM_OTHER;
		}

		return $platform;
	}

	/**
	 * Получить версию приложения
	 *
	 * @param string|null $user_agent
	 *
	 * @return string
	 */
	public static function getAppVersion(string $user_agent = null):string {

		if (is_null($user_agent)) {
			$user_agent = self::getUserAgent();
		}

		$user_agent = mb_strtolower($user_agent);

		// если не нашли версию, то говорим, что у пользователя неизвестная версия
		preg_match("~\(([^()]*)\)~", $user_agent, $matches);

		if (count($matches) < 1) {
			return self::APP_VERSION_UNKNOWN;
		}

		return $matches[1];
	}

	/**
	 * Доступна ли платформа
	 *
	 * @param string $platform
	 *
	 * @return bool
	 */
	public static function isPlatformAvailable(string $platform):bool {

		return in_array($platform, self::_AVAILABLE_PLATFORM_LIST);
	}

	/**
	 * Проверяем, что пользователь зашел с известной нам платформы
	 *
	 * @param string $platform
	 *
	 * @return void
	 * @throws PlatformNotFoundException
	 */
	public static function assertPlatformAvailable(string $platform):void {

		if (!in_array($platform, self::_AVAILABLE_PLATFORM_LIST)) {
			throw new PlatformNotFoundException("unknown client platform");
		}
	}

	/**
	 * Получить название приложения из User-Agent
	 *
	 * @param string|null $user_agent
	 *
	 * @return string
	 */
	public static function getAppName(string $user_agent = null):string {

		if (is_null($user_agent)) {
			$user_agent = self::getUserAgent();
		}

		if (mb_strlen($user_agent) < 1) {
			return $user_agent;
		}
		return mb_strtolower(strtok($user_agent, " -"));
	}

	/**
	 * Получить полную информацию по user agent пользователя
	 *
	 * @param string|null $user_agent
	 *
	 * @return array
	 */
	#[ArrayShape([
		"app_version" => "string",
		"platform"    => "string",
		"user_agent"  => "null|string",
	])]
	public static function getFullInfo(string $user_agent = null):array {

		if (is_null($user_agent)) {
			$user_agent = self::getUserAgent();
		}

		return [
			"app_version" => self::getAppVersion($user_agent),
			"platform"    => self::getPlatform($user_agent),
			"user_agent"  => $user_agent,
		];
	}

	/**
	 * Вернуть список поддерживаемых платформ
	 *
	 * @return string[]
	 */
	public static function getAvailablePlatformList():array {

		return self::_AVAILABLE_PLATFORM_LIST;
	}

	/**
	 * Вернуть список поддерживаемых типов приложения
	 *
	 * @param string|null $app_name
	 *
	 * @return void
	 * @throws AppNameNotFoundException
	 */
	public static function assertAppNameAvailable(string $app_name = null):void {

		// если не передали имя приложения - берем его из user-agent
		if (is_null($app_name)) {
			$app_name = self::getAppName();
		} else {
			$app_name = mb_strtolower($app_name);
		}

		if (!in_array($app_name, self::_AVAILABLE_APP_NAME_LIST)) {
			throw new AppNameNotFoundException("cant find app name in available list");
		}
	}

	public static function getAvailableAppNameList():array {

		return self::_AVAILABLE_APP_NAME_LIST;
	}

	/**
	 * Найти и вернуть пришедшую платформу из списка доступных
	 *
	 * @param string $user_agent
	 *
	 * @return string
	 */
	protected static function _getPlatformFromList(string $user_agent):string {

		// проходимся по всем доступным платформам
		foreach (self::_AVAILABLE_PLATFORM_LIST as $v) {

			// если платформа совпадает, то выходим из цикла
			if (stristr(mb_strtolower($user_agent), $v) !== false) {
				return $v;
			}
		}

		return "";
	}
}