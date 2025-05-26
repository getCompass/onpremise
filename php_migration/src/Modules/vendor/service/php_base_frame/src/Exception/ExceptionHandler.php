<?php

namespace BaseFrame\Exception;

/**
 * Класс для обработки неотловленных исключений.
 * Позволяет корректно завершить работу логики, если вылетает исключение.
 */
class ExceptionHandler {

	/** @var ExceptionHandler|null экземпляр синглтона */
	protected static ?ExceptionHandler $_instance = null;

	/** @var callable[] список колбэков */
	protected array $_callback_list = [];

	/**
	 * Закрываем конструктор для синглтона.
	 */
	protected function __construct() {

		// добавляем общий колбэк для обработки исключений
		set_exception_handler(fn(object $ex) => $this->onExceptionLeaked($ex));
	}

	/**
	 * Добавляет новый callback на обработку непойманного исключения.
	 *
	 * @param callable $exception_callback
	 * @param bool     $need_prepend
	 * @return void
	 */
	public static function register(callable $exception_callback, bool $need_prepend = false):void {

		if (is_null(static::$_instance)) {
			static::$_instance = new static();
		}

		if ($need_prepend) {
			array_unshift(static::$_instance->_callback_list, $exception_callback);
		} else {
			static::$_instance->_callback_list[] = $exception_callback;
		}
	}

	/**
	 * Вызывает все зарегистрированные обработчики.
	 * @param object $ex
	 * @return void
	 */
	protected function onExceptionLeaked(object $ex):void {

		foreach ($this->_callback_list as $callback) {
			$callback($ex);
		}

		// дефолтно вызываем стандартный обработчик
		\baseExceptionHandler::work($ex);
	}
}