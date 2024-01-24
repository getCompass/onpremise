<?php

namespace Compass\FileNode;

/**
 * Основной класс для работы с пользователем
 * если пользователь залогинен его User::init()->user_id > 0, если нет то = 0
 */
class User {

	public int    $user_id      = 0;  // собственно user_id данного пользователя, все классы берут user_id только отсюда
	public string $session_uniq = ""; // session_uniq данного пользователя, все классы берут session_uniq только отсюда

	// для инициализации пользователя
	public static function init(int $user_id = 0):self {

		if (!isset($GLOBALS[__CLASS__])) {
			$GLOBALS[__CLASS__] = [];
		}

		if (isset($GLOBALS[__CLASS__][$user_id])) {
			return $GLOBALS[__CLASS__][$user_id];
		}

		$GLOBALS[__CLASS__][$user_id] = new User($user_id);

		return $GLOBALS[__CLASS__][$user_id];
	}

	/**
	 * User constructor.
	 */
	function __construct(int $user_id) {

		$this->user_id = $user_id;
	}

	// используется в unit тестах, когда много методов подряд должны вызывать разных пользователей
	public static function end():void {

		if (isset($GLOBALS[__CLASS__]) && is_array($GLOBALS[__CLASS__])) {

			foreach ($GLOBALS[__CLASS__] as $key => $value) {

				unset($value);
				unset($GLOBALS[__CLASS__][$key]);
			}
		}
	}
}
