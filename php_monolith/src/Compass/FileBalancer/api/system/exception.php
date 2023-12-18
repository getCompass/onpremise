<?php

namespace Compass\FileBalancer;

/**
 * Системный файл — не менять!
 */

// класс для обработки и реакции на исключения
// в зависимсости от применения фреймворка в нем могут быть множенство своих типо исключений и реакций на них
// полезная информацию:
//
//	https://github.com/codedokode/pasta/blob/master/php/exceptions.md
// 	https://habrahabr.ru/post/99431/
//	https://habrahabr.ru/post/100137/
//	https://true-coder.ru/oop-v-php/oop-v-php-isklyucheniya.html
//	https://ruseller.com/lessons.php?rub=37&id=945
//

// базое исключение которое используется везде внутри феймворка
// это необходимо, чтобы отличать свои исключения от не своих
class baseException extends \Exception {

}

// ----
// все остальные исключения наследуются от базового класса
// ----

// функция вернула не то, что ожидали. используется при проверке ожидаемых значений из моделей / контролеров
class returnException extends baseException {

	const DEFAULT_HTTP_CODE = HTTP_CODE_500;
}

// разработчик передал в метод не то, что ожидалось, или ошибка в коде
class parseException extends baseException {

	const DEFAULT_HTTP_CODE = HTTP_CODE_500;
}

// исключение при работе с базой данных, когда отправили не безопасный или ошибочный запрос
class queryException extends baseException {

	const DEFAULT_HTTP_CODE = HTTP_CODE_500;
}

// когда не передали обязательный параметр в post запросе, или передаваемый тип не соотвествовал строго ожидаемому.
class paramException extends baseException {

	const DEFAULT_HTTP_CODE       = HTTP_CODE_400;
	const DONT_WRITE_CRITICAL_LOG = true;
}

// вопросы доступа юзера (просрочка сесии и т/п/)
class userAccessException extends baseException {

	// не пишем крит логи, так как это не критично
	const DONT_WRITE_CRITICAL_LOG = true;
	const DEFAULT_HTTP_CODE       = HTTP_CODE_401;
}

// вопросы доступа / корректности к API (не логируется)
class apiAccessException extends baseException {

	// не пишем крит логи, так как это не критично
	const DONT_WRITE_CRITICAL_LOG = true;
	const DEFAULT_HTTP_CODE       = HTTP_CODE_404;
}

// вопросы доступа / корректности к SOCKET API
class socketAccessException extends baseException {

	const DONT_WRITE_CRITICAL_LOG = false;
	const DEFAULT_HTTP_CODE       = HTTP_CODE_403;
}

// при работе с шиной и другими сервисами
class busException extends baseException {

	const DEFAULT_HTTP_CODE = HTTP_CODE_500;
}

// при срабатыании блокировки
class blockException extends baseException {

	const DONT_WRITE_CRITICAL_LOG = true;
	const DEFAULT_HTTP_CODE       = HTTP_CODE_423;
}

// ------------------------------------------------------------------
// ПЕРЕХВАТ И ОБРАБОТКА ИСКЛЮЧЕНИЙ
// ------------------------------------------------------------------

// любая ошибка или предупреждение приведут к выбросу исключения
// @mixed
set_error_handler(function($errno, $errstr, $errfile, $errline) {

	// не выбрасываем исключение если ошибка подавлена с
	// помощью оператора @
	if (!error_reporting()) {
		return;
	}

	throw new Error($errstr, $errno);
});

// логирование исключений классом
// @mixed
set_exception_handler(function($e) {

	console("ATTENTION! NEW EXCEPTION!");
	baseExceptionHandler::work($e);
});

// класс для обработки брошенных исключений
class baseExceptionHandler {

	// ! основная функция, которая вызывается, когда бросили исключение (любое исключение попадает сюда)
	public static function work(object $exception):void {

		$is_error = $exception instanceof Error;

		// получаем http код
		$http_code = $is_error ? HTTP_CODE_500 : (defined(get_class($exception) . "::DEFAULT_HTTP_CODE") ? $exception::DEFAULT_HTTP_CODE : HTTP_CODE_500);

		// формируем сообщение
		$message = self::_makeMessage($exception, $http_code);

		// записываем ошибку в лог
		self::_writeExceptionToLogs($exception, $message, $is_error);

		// добавляем response_code к ответу
		http_response_code($http_code);

		// отображаем ошибку
		self::_showError($http_code, $exception->getMessage());

		// закрываем соединения
		self::_doShardingEnd();

		exit();
	}

	// формируем сообщение
	protected static function _makeMessage(object $exception, int $http_code):string {

		// формируем сообщение
		$message = self::_getMessageAsString($exception, $http_code);
		$trace   = self::_getTraceAsString($exception);
		$message = "{$message}\n|\n{$trace}\n-----\n";

		return $message;
	}

	// возвращает сообщение
	protected static function _getMessageAsString(object $exception, int $code):string {

		$class_name  = get_class($exception);
		$date        = date(DATE_FORMAT_FULL_S);
		$err_message = $exception->getMessage();
		$line        = $exception->getLine();
		$file        = $exception->getFile();

		$message = "[$class_name], code {$code}, $date\n$err_message\n{$file} on line {$line}";
		return $message;
	}

	// форматирует trace
	protected static function _getTraceAsString(object $exception):string {

		$rtn   = "";
		$count = 0;
		foreach (array_reverse($exception->getTrace()) as $frame) {
			$args = "";
			if (isset($frame["args"])) {
				$args = self::_addTypeToFunctionArguments($frame);
			}
			$rtn .= sprintf("#%s %s(%s): %s(%s)\n", $count, isset($frame["file"]) ? $frame["file"] : "unknown file", isset($frame["line"]) ? $frame["line"] : "unknown line", (isset($frame["class"])) ? $frame["class"] . $frame["type"] . $frame["function"] : $frame["function"], $args);
			$count++;
		}

		$rtn = str_replace(PATH_ROOT, "/", $rtn);
		return $rtn;
	}

	// получает тип данных аргумента функции
	protected static function _addTypeToFunctionArguments(array $frame):string {

		$args = [];
		foreach ($frame["args"] as $arg) {
			if (is_string($arg)) {
				$args[] = "'" . $arg . "'";
			} elseif (is_array($arg)) {
				$args[] = "Array";
			} elseif (is_null($arg)) {
				$args[] = "NULL";
			} elseif (is_bool($arg)) {
				$args[] = ($arg) ? "true" : "false";
			} elseif (is_object($arg)) {
				$args[] = get_class($arg);
			} elseif (is_resource($arg)) {
				$args[] = get_resource_type($arg);
			} else {
				$args[] = $arg;
			}
		}
		$args = join(", ", $args);

		return $args;
	}

	// записываем ошибку в лог
	protected static function _writeExceptionToLogs(object $exception, string $message, bool $is_error):void {

		// заносим в логи exception
		file_put_contents(CONFIG_LOG_EXCEPTION_PATH . (get_class($exception)) . ".log", $message . "\n", FILE_APPEND);

		// пишем критические логи
		if (!$is_error && (!defined(get_class($exception) . "::DONT_WRITE_CRITICAL_LOG") || $exception::DONT_WRITE_CRITICAL_LOG !== true)) {
			file_put_contents(LOG_CRITICAL_PHP_EXCEPTION, $message . "\n", FILE_APPEND);
		}

		// если пришла ошибка, вместо исключения - пишем ее
		if ($is_error) {
			file_put_contents(LOG_ERROR_PHP, $message . "\n", FILE_APPEND);
		}
	}

	// отображаем ошибку
	protected static function _showError(int $http_code, string $message):void {

		// если работаем из консоли
		if (isCLi()) {
			return;
		}

		// если не нужно показывать текст ошибки
		if (!DISPLAY_ERRORS) {

			$message = "Forbidden! Production server not allowed error message.";
		}

		showAjax([
			"status"    => "error",
			"http_code" => $http_code,
			"message"   => $message,
		]);
	}

	// закрываем соединения
	protected static function _doShardingEnd():void {

		// закрываем соединения
		try {
			@sharding::end();
		} catch (exception $_) {
			// nothing
		};
	}
}