<?php

namespace Compass\FileBalancer;

// класс для работы с go_usercache и таблицами user
class Type_User_Main {

	// -------------------------------------------------------
	// EXTRA VARIABLES
	// -------------------------------------------------------

	protected const _EXTRA_VERSION = 2; // текущая версия extra
	protected const _EXTRA_SCHEMA  = [  // массив с версиями extra

		1 => [
			"is_email_attached" => 0, // привязан ли почтовый адрес у пользователя
			"is_disabled"       => 0, // флаг овтечающий за деактивацию аккаунт пользователя
		],

		2 => [
			"is_email_attached" => 0,  // привязан ли почтовый адрес у пользователя
			"is_disabled"       => 0,  // флаг овтечающий за деактивацию аккаунт пользователя
			"badge"             => [
				"content"  => "",
				"color_id" => 0,
			], // цвет и текст баджа
		],
	];

	// -------------------------------------------------------
	// EXTRA METHODS
	// -------------------------------------------------------

	// возвращает текущую структуру extra с default значениями
	public static function initExtra():array {

		$output = [
			"handler_version"   => self::_EXTRA_VERSION,
			"extra"             => self::_EXTRA_SCHEMA[self::_EXTRA_VERSION],
		];
		return $output;
	}

	// проверяем, что у пользователя привязана почта
	public static function isEmailAttached(array $extra):bool {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		return $extra["extra"]["is_email_attached"] == 1 ? true : false;
	}

	// проверяем, активен ли аккаунт пользователя
	public static function isDisabledProfile(array $extra):bool {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		return $extra["extra"]["is_disabled"] == 1 ? true : false;
	}

	// помечаем почту профиля привязанной
	public static function setEmailAttached(array $extra):array {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// обновляем флаг
		$extra["extra"]["is_email_attached"] = 1;

		return $extra;
	}

	// помечаем почту профиля пользователя не привязанной
	public static function setEmailDetached(array $extra):array {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// обновляем флаг
		$extra["extra"]["is_email_attached"] = 0;

		return $extra;
	}

	// помечаем профиль деактивированным
	public static function setProfileDisabled(array $extra):array {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// обновляем флаг
		$extra["extra"]["is_disabled"] = 1;
		return $extra;
	}

	// помечаем профиль разблокированным
	public static function setProfileEnabled(array $extra):array {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// обновляем флаг
		$extra["extra"]["is_disabled"] = 0;
		return $extra;
	}

	// устанавливаем badge
	public static function setBadge(array $extra, int $color_id, string $content):array {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// обновляем флаг
		$extra["extra"]["badge"]["color_id"] = $color_id;
		$extra["extra"]["badge"]["content"]  = $content;
		return $extra;
	}

	// получаем цвет badge
	public static function getBadgeColor(array $extra):int {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// обновляем флаг
		return $extra["extra"]["badge"]["color_id"];
	}

	// получаем content badge
	public static function getBadgeContent(array $extra):string {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// обновляем флаг
		return $extra["extra"]["badge"]["content"];
	}

	// удаляем badge
	public static function clearBadge(array $extra):array {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		// удаляем badge
		$extra["extra"]["badge"] = self::_EXTRA_SCHEMA[self::_EXTRA_VERSION]["badge"];

		return $extra;
	}

	// проверяем существует ли badge
	public static function isBadgeExist(array $extra):bool {

		// актуализируем структуру
		$extra = self::_getExtra($extra);

		return isset($extra["extra"]["badge"]["content"]) && $extra["extra"]["badge"]["content"] != "";
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	// актуализирует структуру extra
	protected static function _getExtra(array $extra):array {

		// сравниваем версию пришедшей extra с текущей
		if ($extra["handler_version"] != self::_EXTRA_VERSION) {

			// сливаем текущую версию extra и ту, что пришла
			$extra["extra"]           = array_merge(self::_EXTRA_SCHEMA[self::_EXTRA_VERSION], $extra["extra"]);
			$extra["handler_version"] = self::_EXTRA_VERSION;
		}
		return $extra;
	}
}
