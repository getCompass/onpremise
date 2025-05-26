<?php

namespace BaseFrame\Error;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * Класс для работы с ошибками
 */
class ErrorHandler {

	private static ErrorHandler|null $_instance = null;
	private ?bool                    $_is_display_error;

	/**
	 * Error constructor.
	 *
	 * @throws ReturnFatalException
	 */
	private function __construct(bool $is_display_error) {

		if (!is_bool($is_display_error)) {
			throw new ReturnFatalException("incorrect is_display_error");
		}

		$this->_is_display_error = $is_display_error;
	}

	/**
	 * инициализируем синглтон
	 *
	 */
	public static function init(bool $is_display_error):static {

		if (!is_null(static::$_instance)) {
			return static::$_instance;
		}

		return static::$_instance = new static($is_display_error);
	}

	/**
	 * Возвращает экземпляр класса.
	 */
	public static function instance():static {

		if (is_null(static::$_instance)) {
			throw new ReturnFatalException("need to initialized before using");
		}

		return static::$_instance;
	}

	/**
	 * получаем is_display_error
	 *
	 */
	public function display():string {

		return $this->_is_display_error;
	}
}
