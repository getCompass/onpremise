<?php

namespace Compass\Pivot;

/**
 * класс для валидации данных в системных методах
 */
class Domain_System_Entity_Validator {

	// максимальная версия
	protected const _MAX_VERSION = 100;

	// доступные локали
	protected const _ALLOW_LANG_LIST = [
		"ru",
		"en",
		"it",
		"fr",
		"es",
		"de",
	];

	/**
	 * Проверяем локаль на корректность
	 */
	public static function assertIncorrectLang(string $lang):void {

		if ((mb_strlen($lang) != 2) || !in_array($lang, self::_ALLOW_LANG_LIST)) {
			throw new cs_IncorrectLang();
		}
	}

	/**
	 * Проверяем версию на корректность
	 */
	public static function assertIncorrectVersion(int $version):void {

		if ($version < 1 || $version > self::_MAX_VERSION) {
			throw new cs_IncorrectVersion();
		}
	}
}
