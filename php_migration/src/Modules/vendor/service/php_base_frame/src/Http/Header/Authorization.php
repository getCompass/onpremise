<?php

namespace BaseFrame\Http\Header;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Заголовок авторизации.
 */
class Authorization extends Header {

	protected const _HEADER_KEY = "AUTHORIZATION"; // ключ хедера

	public const AUTH_TYPE_NONE   = "None";
	public const AUTH_TYPE_BEARER = "Bearer";

	protected const _KNOWN_TYPES = [self::AUTH_TYPE_BEARER, self::AUTH_TYPE_NONE];

	protected static self|null $_instance       = null;
	protected static bool      $_is_invalidated = false;

	protected bool $_is_set     = false;
	protected bool $_is_correct = false;

	protected string $_type = "";
	protected string $_key  = "";

	/**
	 * Заголовок авторизации.
	 */
	protected function __construct() {

		parent::__construct();
		$this->_is_set = static::isSet();
	}

	/**
	 * Пытается получить заголовок авторизации, если него нет, возвращает false.
	 */
	public static function parse():static|false {

		if (static::$_is_invalidated) {
			return false;
		}

		if (is_null(static::$_instance)) {
			static::$_instance = new static();
		}

		if (!static::$_instance->_parse()) {
			return false;
		}

		return static::$_instance;
	}

	/**
	 * Парсит заголовок для дальнейшей работы.
	 */
	protected function _parse():bool {

		if (!$this->_is_set) {
			return false;
		}

		$chunk_list = explode(" ", $this->getValue(), 2);

		if (!in_array($chunk_list[0], static::_KNOWN_TYPES)) {
			return false;
		}

		$this->_is_correct = true;

		$this->_type = $chunk_list[0];
		$this->_key  = $chunk_list[1] ?? "";

		return true;
	}

	/**
	 * Инвалидирует заголовок, после инвалидации при попытке обращения
	 * к к заголовку он будет вести себя так, будто не был установлен в запросе
	 */
	public static function invalidate():void {

		static::$_is_invalidated = true;
	}

	/**
	 * Проверяет, был ли установлен заголовок при запросе.
	 */
	public static function isSet():bool {

		return isset($_SERVER["HTTP_" . static::_HEADER_KEY]);
	}

	/**
	 * Возвращает наличие заголовка в запросе.
	 */
	public function isNone():bool {

		if (!$this->_is_set || !$this->isCorrect()) {
			throw new ReturnFatalException("authorization header is not set or incorrect");
		}

		if (static::$_is_invalidated) {
			return true;
		}

		return $this->getType() === static::AUTH_TYPE_NONE;
	}

	/**
	 * Возвращает статус парсинга заголовка, вернет истину,
	 * только если заголовок имеет ожидаемый формат.
	 */
	public function isCorrect():bool {

		return $this->_is_correct;
	}

	/**
	 * Возвращает тип токена авторизации. По нему можно определить
	 * соответствующий ключ из реализации через cookie.
	 */
	public function getType():string {

		if (!$this->_is_set || !$this->isCorrect()) {
			throw new ReturnFatalException("authorization header is not set or incorrect");
		}

		if (static::$_is_invalidated) {
			throw new ReturnFatalException("authorization header was invalidated");
		}

		return $this->_type;
	}

	/**
	 * Возвращает токен авторизации. По сути ключ сессии.
	 */
	public function getToken():string {

		if (!$this->_is_set || !$this->isCorrect()) {
			throw new ReturnFatalException("authorization header is not set or incorrect");
		}

		if (static::$_is_invalidated) {
			throw new ReturnFatalException("authorization header was invalidated");
		}

		return $this->_key;
	}
}