<?php

namespace Compass\Conversation;

/**
 * Класс для работы со структурой поля extra
 */
class Type_Conversation_ThreadRel_Extra {

	// версия упаковщика
	protected const _EXTRA_VERSION = 2;

	// схема extra
	protected const _EXTRA_SCHEMA = [

		1 => [
			"thread_hidden_user_list" => [], // пользователи, скрывшие тред
		],

		2 => [
			"thread_hidden_user_list"        => [], // пользователи, скрывшие тред
			"is_thread_hidden_for_all_users" => 0,  // тред скрыт для всех пользователей
		],
	];

	/**
	 * Добавить пользователя в список, кто скрыл тред
	 *
	 */
	public static function addUserToHideList(array $extra, int $user_id):array {

		$extra = self::_getExtra($extra);

		if (!in_array($user_id, $extra["extra"]["thread_hidden_user_list"])) {
			array_push($extra["extra"]["thread_hidden_user_list"], $user_id);
		}

		return $extra;
	}

	/**
	 * Очистить список пользователей, кто скрыл тред
	 *
	 */
	public static function clearHideUserList(array $extra):array {

		$extra                                     = self::_getExtra($extra);
		$extra["extra"]["thread_hidden_user_list"] = [];

		return $extra;
	}

	/**
	 * Получить список пользователей, кто скрыл тред
	 *
	 */
	public static function getHideUserList(array $extra):array {

		$extra = self::_getExtra($extra);
		return $extra["extra"]["thread_hidden_user_list"];
	}

	/**
	 * устанавливаем флаг скрытия треда у всех пользователей
	 */
	public static function setThreadHiddenForAllUsers(array $extra, bool $value):array {

		$extra                                            = self::_getExtra($extra);
		$extra["extra"]["is_thread_hidden_for_all_users"] = $value ? 1 : 0;
		return $extra;
	}

	/**
	 * возвращаем значение флага скрытия треда для всех пользователей
	 */
	public static function isThreadHiddenForAllUsers(array $extra):bool {

		$extra = self::_getExtra($extra);

		return $extra["extra"]["is_thread_hidden_for_all_users"] == 1;
	}

	/**
	 * Создать структуру extra
	 */
	public static function initExtra():array {

		return [
			"version" => self::_EXTRA_VERSION,
			"extra"   => self::_EXTRA_SCHEMA[self::_EXTRA_VERSION],
		];
	}

	// -------------------------------------------------------
	// PROTECTED
	// -------------------------------------------------------

	/**
	 * Получить актуальную структуру extra
	 *
	 */
	protected static function _getExtra(array $extra):array {

		// если версия не совпадает - дополняем её до текущей
		if (!isset($extra["version"])) {
			return self::initExtra();
		}

		if ($extra["version"] != self::_EXTRA_VERSION) {

			$extra["extra"]   = array_merge(self::_EXTRA_SCHEMA[self::_EXTRA_VERSION], $extra["extra"]);
			$extra["version"] = self::_EXTRA_VERSION;
		}

		return $extra;
	}
}