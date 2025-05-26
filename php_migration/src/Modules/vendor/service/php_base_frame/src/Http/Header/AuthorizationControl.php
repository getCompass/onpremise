<?php

namespace BaseFrame\Http\Header;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Заголовок контроля авторизации.
 */
class AuthorizationControl extends Header {

	/** @var string ключ заголовка */
	protected const _HEADER_KEY = "AUTHORIZATION_CONTROL";

	/** @var string нужно ли очищать данные авторизации при ошибке авторизации сессии */
	public const _POLICY_NO_CLEAR = "no-clear";

	/** @var string нужно ли подавлять 401 ошибку */
	public const _POLICY_SUPPRESS_401 = "suppress-401";

	protected static self|null $_instance = null;

	protected bool $_no_clear     = false;
	protected bool $_suppress_401 = false;

	/**
	 * Возвращает заголовок контроля авторизации. Если заголовка нет
	 * или его не удалось распарсить, будут использованы значения по умолчанию.
	 */
	public static function parse():static|false {

		if (is_null(static::$_instance)) {

			static::$_instance = new static();
			static::$_instance->_parse();
		}

		return static::$_instance;
	}

	/**
	 * Парсит данные заголовка для дальнейшей работы.
	 */
	protected function _parse():bool {

		$value      = str_replace(" ", "", $this->getValue());
		$chunk_list = explode(",", $value);

		foreach ($chunk_list as $chunk) {

			if ($chunk === static::_POLICY_NO_CLEAR) {
				$this->_no_clear = true;
			}

			if ($chunk === static::_POLICY_SUPPRESS_401) {
				$this->_suppress_401 = true;
			}
		}

		return true;
	}

	/**
	 * Проверяет, был ли установлен заголовок при запросе.
	 */
	public static function isSet():bool {

		return isset($_SERVER["HTTP_" . static::_HEADER_KEY]);
	}

	/**
	 * Возвращает политику очистки данных авторизации.
	 */
	public static function needClear():bool {

		return !static::$_instance->_no_clear;
	}

	/**
	 * Возвращает политику отправки http-кода 401.
	 */
	public static function expect401():bool {

		return !static::$_instance->_suppress_401;
	}
}