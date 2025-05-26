<?php

use \BaseFrame\Path\PathProvider;
use \BaseFrame\Error\ErrorProvider;

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
class baseException extends Exception {
	const DONT_WRITE_CRITICAL_LOG = false;
}

// ----
// все остальные исключения наследуются от базового класса
// ----

/**
 * функция вернула не то, что ожидали. используется при проверке ожидаемых значений из моделей / контролеров
 */
class returnException extends baseException {

	const DEFAULT_HTTP_CODE = HTTP_CODE_500;
}

/**
 * разработчик передал в метод не то, что ожидалось, или ошибка в коде
 */
class parseException extends baseException {

	const DEFAULT_HTTP_CODE = HTTP_CODE_500;
}

/**
 * исключение при работе с базой данных, когда отправили не безопасный или ошибочный запрос
 */
class queryException extends baseException {

	const DEFAULT_HTTP_CODE = HTTP_CODE_500;
}

/**
 * когда не передали обязательный параметр в post запросе, или передаваемый тип не соотвествовал строго ожидаемому.
 */
class paramException extends baseException {

	const DEFAULT_HTTP_CODE       = HTTP_CODE_400;
	const DONT_WRITE_CRITICAL_LOG = true;
}

/**
 * вопросы доступа юзера (просрочка сесии и т/п/)
 */
class userAccessException extends baseException {

	// не пишем крит логи, так как это не критично
	const DONT_WRITE_CRITICAL_LOG = true;
	const DEFAULT_HTTP_CODE       = HTTP_CODE_401;
}

/**
 * вопросы доступа / корректности к API (не логируется)
 */
class apiAccessException extends baseException {

	// не пишем крит логи, так как это не критично
	const DONT_WRITE_CRITICAL_LOG = true;
	const DEFAULT_HTTP_CODE       = HTTP_CODE_404;
}

/**
 * вопросы доступа / корректности к SOCKET API
 */
class socketAccessException extends baseException {

	const DONT_WRITE_CRITICAL_LOG = false;
	const DEFAULT_HTTP_CODE       = HTTP_CODE_403;
}

/**
 * при работе с шиной и другими сервисами
 */
class busException extends baseException {

	const DEFAULT_HTTP_CODE = HTTP_CODE_500;
}

/**
 * при срабатыании блокировки
 */
class blockException extends baseException {

	const DONT_WRITE_CRITICAL_LOG = true;
	const DEFAULT_HTTP_CODE       = HTTP_CODE_423;

	private int $expire = 0;

	/**
	 * blockException constructor.
	 *
	 * @param string         $message
	 * @param int            $expire
	 * @param int            $code
	 * @param Throwable|null $previous
	 */
	public function __construct(string $message = "", int $expire = 0, int $code = 0, Throwable $previous = null) {

		$this->expire = $expire;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return int
	 */
	public function getExpire():int {

		return $this->expire;
	}

	/**
	 * @param int $expire
	 */
	public function setExpire(int $expire):void {

		$this->expire = $expire;
	}
}

/**
 * exception для разруливания ошибок curl
 */
class cs_CurlError extends Exception {

}

/**
 * добавляем возможность установить индекс к ошибке
 * @mixed
 */
class cs_ExceptionWithIndex extends Exception {

	protected int $_index;

	public function __construct(int $index = 0, $message = "", $code = 0, Throwable $previous = null) {

		$this->_index = $index;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @return int
	 */
	public function getIndex():int {

		return $this->_index;
	}

	/**
	 * @param int $index
	 */
	public function setIndex(int $index):void {

		$this->_index = $index;
	}
}

/**
 * невалидный номер телефона
 */
class cs_InvalidPhoneNumber extends cs_ExceptionWithIndex {

}

/**
 * невалидный имя пользователя
 */
class cs_InvalidProfileName extends cs_ExceptionWithIndex {

}

/**
 * Пользователь не участник
 */
class cs_UserIsNotMember extends cs_ExceptionWithIndex {

}

/**
 * Нельзя устанавливать такую роль
 */
class cs_CompanyUserIncorrectRole extends \Exception {

}

/**
 * Попытка изменить свою роль
 */
class cs_UserChangeSelfRole extends \Exception {

}

/**
 * запись в мемкеш существует
 */
class cs_MemcacheRowIfExist extends Exception {

}

/**
 * ошибка неверно переданого значения UUID
 */
class cs_InvalidUuidVersionException extends Exception {

}

/**
 * ошибка, не смогли распаковать ключ
 */
class cs_UnpackHasFailed extends Exception {

}

/**
 * ошибка, не смогли раскодировать ключ
 */
class cs_DecryptHasFailed extends Exception {

}

/**
 * ошибка, не вернулись данные
 */
class cs_RowIsEmpty extends Exception {

}

/**
 * ошибка, пользователь не является овнером
 */
class cs_CompanyUserIsNotOwner extends Exception {

}

/**
 * ошибка, сессия не найдена
 */
class cs_SessionNotFound extends Exception {

}

/**
 * неудачный socket-запрос
 */
class cs_SocketRequestIsFailed extends Exception {

	protected int    $_http_status_code;
	protected string $_url;
	protected array  $_response;

	/**
	 * Construct the exception. Note: The message is NOT binary safe.
	 *
	 * @link https://php.net/manual/en/exception.construct.php
	 *
	 * @param string         $message  [optional] The Exception message to throw.
	 * @param int            $code     [optional] The Exception code.
	 * @param Throwable|null $previous [optional] The previous throwable used for the exception chaining.
	 */
	public function __construct(int $http_status_code, string $url, array $response, string $message = "", int $code = 0, Throwable|null $previous = null) {

		$this->_http_status_code = $http_status_code;
		$this->_url              = $url;
		$this->_response         = $response;

		parent::__construct($message, $code, $previous);
	}

	public function getHttpStatusCode():int {

		return $this->_http_status_code;
	}

	public function getUrl():string {

		return $this->_url;
	}

	public function getResponse():array {

		return $this->_response;
	}
}

// ------------------------------------------------------------------
// ПЕРЕХВАТ И ОБРАБОТКА ИСКЛЮЧЕНИЙ
// ------------------------------------------------------------------

// любая ошибка или предупреждение приведут к выбросу исключения
// @mixed
set_error_handler("errorHandler");

/**
 * отправляем задачу в error handler
 *
 * @param int    $error_code
 * @param string $error_string
 */
function errorHandler(int $error_code, string $error_string):void {

	// не выбрасываем исключение если ошибка подавлена с
	// помощью оператора @
	if (!error_reporting()) {
		return;
	}

	throw new Error($error_string, $error_code);
}

// логирование исключений классом
set_exception_handler("exceptionHandler");

/**
 * отправляем задачу в хендлер exception
 *
 * @param $e
 *
 * @mixed
 */
function exceptionHandler($e) {

	console("ATTENTION! NEW EXCEPTION!");
	baseExceptionHandler::work($e);
}

// добавляем обработчик неотлавливаемых ошибок, чтобы они хотя бы в логах отображались
// чудесный и предсказуемый php, где еще такое может быть
\BaseFrame\Exception\ShutdownHandler::register(static function() {

	$error = error_get_last();

	// если ошибки нет, или она не относится к неуловимым, то ничего не делаем
	if ($error === null || !in_array($error["type"], [E_ERROR, E_CORE_ERROR, E_USER_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR, E_PARSE], true)) {
		return;
	}

	$text = sprintf(
		"[ElusiveError] Code 500, %s\nElusive error: %s code %d\nFile: %s:%d\n\n-----\n",
		date(DATE_FORMAT_FULL_S),
		$error["message"],
		$error["type"],
		$error["file"],
		$error["line"],
	);

	// пользуемся стандартным выводом ошибок для исключений
	console("ATTENTION! NEW EXCEPTION!");
	baseExceptionHandler::writeExceptionToLogs(new Error($error["message"], $error["type"]), $text, false);
});

/**
 * класс для обработки брошенных исключений
 */
class baseExceptionHandler {

	// ! основная функция, которая вызывается, когда бросили исключение (любое исключение попадает сюда)
	public static function work(object $exception):void {

		$is_error = $exception instanceof Error;

		$class_http_code = HTTP_CODE_500;
		if (method_exists($exception, "getHttpCode")) {
			$class_http_code = $exception->getHttpCode();
		}
		if (defined(get_class($exception) . "::DEFAULT_HTTP_CODE")) {
			$class_http_code = $exception::DEFAULT_HTTP_CODE;
		}

		// получаем http код
		$http_code = $is_error ? HTTP_CODE_500 : $class_http_code;

		// формируем сообщение
		$message = self::_makeMessage($exception, $http_code);
		console($message);

		// записываем ошибку в лог
		self::writeExceptionToLogs($exception, $message, $is_error);

		// добавляем response_code к ответу
		http_response_code($http_code);

		// отображаем ошибку
		self::_showError($http_code, $exception->getMessage());

		// закрываем соединения
		self::_doShardingEnd();

		exit(1);
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

		return "[$class_name], code {$code}, $date\n$err_message\n{$file} on line {$line}";
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

		$rtn = str_replace(PathProvider::root(), "/", $rtn);
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
	public static function writeExceptionToLogs(object $exception, string $message, bool $is_error):void {

		// заносим в логи exception
		writeToFile(PathProvider::configLogException() . (get_class($exception)) . ".log", $message . "\n");

		// пишем критические логи
		if (!$is_error && !($exception instanceof \BaseFrame\Exception\BaseException)
			&& (!defined(get_class($exception) . "::DONT_WRITE_CRITICAL_LOG") || ($exception::DONT_WRITE_CRITICAL_LOG != true))
			|| (method_exists($exception, "getIsCritical") && $exception->getIsCritical())) {
			writeToFile(PathProvider::logCriticalPhpException(), $message . "\n");
		}

		// если пришла ошибка, вместо исключения - пишем ее
		if ($is_error) {
			writeToFile(PathProvider::logErrorPhp(), $message . "\n");
		}
	}

	// отображаем ошибку
	protected static function _showError(int $http_code, string $message):void {

		// если работаем из консоли
		if (isCLi()) {
			return;
		}

		// если не нужно показывать текст ошибки
		if (!ErrorProvider::display()) {

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
		} catch (exception) {
			// nothing
		};
	}
}
