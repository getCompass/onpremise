<?php

/**
 * основной класс для работы с пользователем
 * если пользователь залогинен его User::init()->user_id > 0, если нет то = 0
 *
 * подсущности пользователя
 */
class User {

	public int    $user_id      = 0;   // собственно user_id данного пользователя, все классы берут user_id только отсюда
	public string $session_uniq = "";  // session_uniq
	public array  $extra        = [];  // массив extra пользователя

	// для инициализации пользователя
	public static function init(int $user_id = 0):self {

		if (!isset($GLOBALS[__CLASS__])) {
			$GLOBALS[__CLASS__] = [];
		}

		if (isset($GLOBALS[__CLASS__][$user_id])) {
			return $GLOBALS[__CLASS__][$user_id];
		}

		$GLOBALS[__CLASS__][$user_id] = new self($user_id);

		return $GLOBALS[__CLASS__][$user_id];
	}

	// конструктор
	function __construct(int $user_id) {

		// если запрос пришел из cli
		if (isCLi()) {

			$this->user_id = $user_id;
			return;
		}

		try {
			[$this->user_id, $this->session_uniq] = Type_Session_Main::getSession();
		} catch (cs_SessionNotFound) {

			// если не нашли сессию в базах то она скорее всего не активна больше
			throw new userAccessException("company_user_session not found");
		}
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