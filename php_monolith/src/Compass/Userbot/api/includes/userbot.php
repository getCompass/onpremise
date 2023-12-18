<?php

namespace Compass\Userbot;

/**
 * основной класс для работы с пользовательским ботом
 *
 */
class Userbot {

	public string $userbot_id = "";
	public int    $status     = 0;
	public string $token      = "";
	public string $secret_key = "";

	// для инициализации бота
	public static function init(string $token):self {

		if (!isset($GLOBALS[__CLASS__])) {
			$GLOBALS[__CLASS__] = [];
		}

		if (isset($GLOBALS[__CLASS__][$token])) {
			return $GLOBALS[__CLASS__][$token];
		}

		$GLOBALS[__CLASS__][$token] = new Userbot($token);

		return $GLOBALS[__CLASS__][$token];
	}

	/**
	 * Userbot constructor.
	 */
	function __construct(string $token) {

		// если запрос пришел из cli
		if (isCLi()) {

			$this->token = $token;
			return;
		}

		$userbot = Type_Userbot_Main::get($token);

		$this->token      = $token;
		$this->userbot_id = $userbot->userbot_id;
		$this->status     = $userbot->status;
		$this->secret_key = $userbot->secret_key;
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
