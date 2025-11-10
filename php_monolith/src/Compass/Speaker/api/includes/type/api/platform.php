<?php

namespace Compass\Speaker;

// класс для управления платформой
// занимается установкой платформы и ее получением
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

	// получение платформы
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
			throw new cs_PlatformNotFound();
		}

		return $platform;
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

	// получение версии платформы
	public static function getVersion(string $user_agent):string {

		$matches = [];
		if (!preg_match("/[\d.]+/m", $user_agent, $matches) || count($matches) < 1) {
			throw new cs_PlatformVersionNotFound();
		}

		return $matches[0];
	}

	// доступна ли платформа
	public static function isPlatformAvailable(string $platform):bool {

		return in_array($platform, self::_AVAILABLE_PLATFORM_LIST);
	}
}