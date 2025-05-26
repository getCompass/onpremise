<?php

namespace BaseFrame\Exception;

use JetBrains\PhpStorm\NoReturn;
use Throwable;

/**
 * Хендлер исключений v2
 */
class BaseExceptionHandler {

	/** @var BaseExceptionHandler|null экземпляр синглтона */
	protected static ?BaseExceptionHandler $_instance = null;

	/**
	 * Скрываем конструктор, работаем через синглтон.
	 */
	protected function __construct() {

	}

	/**
	 * Метод инстанцирования экземпляра.
	 */
	public static function instance():static {

		if (is_null(static::$_instance)) {

			static::$_instance = new static();
		}

		return static::$_instance;
	}

	/**
	 * Функция для обработки исключений приложения
	 *
	 * @param BaseException $exception
	 *
	 * @return void
	 */
	#[NoReturn]
	public function work(Throwable $exception):void {

		// раз исключение попало сюда, значит 500
		$http_code  = HTTP_CODE_500;

		// для ошибок уровня request
		if ($exception instanceof RequestException) {
			$http_code  = $exception->getHttpCode();
		}

		// формируем сообщение
		$message = ExceptionUtils::makeMessage($exception, $http_code);
		console($message);

		// записываем ошибку в лог
		ExceptionUtils::writeExceptionToLogs($exception, $message);

		// отображаем ошибку
		ExceptionUtils::showError($http_code);

		// закрываем соединения
		ExceptionUtils::doShardingEnd();

		exit(1);
	}
}