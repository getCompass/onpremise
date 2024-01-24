<?php

namespace Compass\FileBalancer;

/**
 * основной класс для работы с пользователем
 * если пользователь залогинен его User::init()->user_id > 0, если нет то = 0
 *
 * * @property Type_Session_Main $session
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
	 *
	 * @throws busException
	 * @throws userAccessException|cs_AnswerCommand|returnException
	 */
	function __construct(int $user_id) {

		// если запрос пришел из cli
		if (isCLi()) {

			$this->user_id = $user_id;
			return;
		}

		try {

			if (CURRENT_SERVER === PIVOT_SERVER) {
				[$this->user_id, $this->session_uniq] = Type_Session_Main::getSessionForPivot();
			}

			if (CURRENT_SERVER === CLOUD_SERVER) {
				[$this->user_id, $this->session_uniq] = Type_Session_Main::getSessionForCompany();
			}
		} catch (cs_SessionNotFound) {

			// если не нашли сессию в базах то она скорее всего не активна больше
			throw new userAccessException("session not found");
		} catch (cs_CookieIsEmpty) {

			// если куки пусты или сессия не валидна просим получить сессию
			throw new cs_AnswerCommand("need_call_start", []);
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
