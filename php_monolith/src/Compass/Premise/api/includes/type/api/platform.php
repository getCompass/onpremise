<?php

namespace Compass\Premise;

/**
 * класс для управления платформой занимается установкой платформы и ее получением
 */
class Type_Api_Platform {

	// текущие возможные платформы
	public const PLATFORM_ELECTRON = "electron";
	public const PLATFORM_ANDROID  = "android";
	public const PLATFORM_IOS      = "iphone";
	public const PLATFORM_IPAD     = "ipad";
	public const PLATFORM_OTHER    = "other";

	// доступные платформы
	protected const _AVAILABLE_PLATFORM_LIST = [
		self::PLATFORM_ELECTRON,
		self::PLATFORM_IOS,
		self::PLATFORM_ANDROID,
		self::PLATFORM_IPAD,
	];

	// текущие возможные OS electron платформы
	public const PLATFORM_ELECTRON_OS_MACOS   = "macos";
	public const PLATFORM_ELECTRON_OS_WINDOWS = "windows";
	public const PLATFORM_ELECTRON_OS_LINUX   = "linux";

	/**
	 * получение платформы
	 *
	 * @throws cs_PlatformNotFound
	 */
	public static function getPlatform(?string $user_agent = null):string {

		// если работаем из консоли и не передали ua - возвращаем платформу other
		if (isCLi() && is_null($user_agent)) {
			return self::PLATFORM_OTHER;
		}

		if (is_null($user_agent)) {
			$user_agent = getUa();
		}

		$user_agent = mb_strtolower($user_agent);

		// ищем платформу в списке доступных
		$platform = self::_getPlatformFromList($user_agent);

		// если не нашли платформу, выбрасываем исключение
		if (strlen($platform) < 1) {
			throw new cs_PlatformNotFound("unknown platform");
		}

		return $platform;
	}

	/**
	 * получаем операционную систему electron платформы по user-agent
	 *
	 * @return string
	 * @throws cs_PlatformNotFound
	 */
	public static function getElectronPlatformOS(?string $user_agent = null):string {

		// получаем платформу
		$platform = self::getPlatform($user_agent);

		// если это не electron, то возвращаем саму платформу
		if ($platform !== Type_Api_Platform::PLATFORM_ELECTRON) {
			return $platform;
		}

		if (is_null($user_agent)) {
			$user_agent = getUa();
		}

		// если в user-agent содержится darwin, то это macos
		if (str_contains($user_agent, "darwin")) {
			return self::PLATFORM_ELECTRON_OS_MACOS;
		}

		// если в user-agent содержится win, то это windows
		if (str_contains($user_agent, "win")) {
			return self::PLATFORM_ELECTRON_OS_WINDOWS;
		}

		// во всех остальных случаях считаем, что это linux
		return self::PLATFORM_ELECTRON_OS_LINUX;
	}

	// ищем пришедшую платформу в списке доступных
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

	// доступна ли платформа
	public static function isPlatformAvailable(string $platform):bool {

		return in_array($platform, self::_AVAILABLE_PLATFORM_LIST);
	}

	/**
	 * Получить название приложения из user_agent
	 *
	 */
	public static function getAppNameByUserAgent(?string $user_agent = null):string {

		if (is_null($user_agent)) {
			$user_agent = getUa();
		}

		if (mb_strlen($user_agent) < 1) {
			return $user_agent;
		}
		return mb_strtolower(strtok($user_agent, " -"));
	}
}