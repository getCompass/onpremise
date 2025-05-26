<?php declare(strict_types=1);

/**
 * Класс для обработки ошибок при завершении работы приложения.
 * Некоторые ошибки не ловятся через set_error_handler, поэтому в дело вступает этот товарищ.
 *
 * Как и любой класс для решения локальных проблем работает через синглтон.
 */
class ShutdownHandler {

	/** @var ShutdownHandler|null экземпляр синглтона */
	protected static ?ShutdownHandler $_instance = null;

	/** @var callable[] список колбэков */
	protected array $_callback_list = [];

	/**
	 * Закрываем конструктор для синглтона.
	 */
	protected function __construct() {

		// добавляем общий колбэк для остановки
		register_shutdown_function(fn() => $this->onShutdown());
	}

	/**
	 * Добавляет новый callback на завершение исполнения работы.
	 *
	 * @param callable $shutdown_callback
	 * @param bool $need_prepend
	 * @return void
	 */
	public static function register(callable $shutdown_callback, bool $need_prepend = false):void {

		if (is_null(static::$_instance)) {
			static::$_instance = new static();
		}

		if ($need_prepend) {
			array_unshift(static::$_instance->_callback_list, $shutdown_callback);
		} else {
			static::$_instance->_callback_list[] = $shutdown_callback;
		}
	}

	/**
	 * Вызывает все зарегистрированные коллбэки.
	 * @return void
	 */
	protected function onShutdown():void {

		foreach ($this->_callback_list as $callback) {
			$callback();
		}
	}
}
