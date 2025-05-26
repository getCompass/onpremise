<?php

namespace BaseFrame\Exception;

use Throwable;
use \BaseFrame\Path\PathProvider;
use \BaseFrame\Error\ErrorProvider;

/**
 * Вспомогательные функции для исключений
 */
class ExceptionUtils {

	/**
	 * Формируем сообщение об ошибке
	 *
	 * @param object $exception
	 * @param int    $http_code
	 *
	 * @return string
	 */
	public static function makeMessage(object $exception, int $http_code):string {

		// формируем сообщение
		$message = self::_getMessageAsString($exception, $http_code);
		$trace   = self::_getTraceAsString($exception);
		return "{$message}\n|\n{$trace}\n-----\n";
	}

	/**
	 * Записываем информацию в лог
	 *
	 * @param Throwable $exception
	 * @param string    $message
	 *
	 * @return void
	 */
	public static function writeExceptionToLogs(Throwable $exception, string $message):void {

		// заносим в логи exception
		writeToFile(PathProvider::configLogException() . (get_class($exception)) . ".log", $message . "\n");

		// пишем критические логи
		if (!($exception instanceof \Error) && ((!($exception instanceof BaseException) || $exception->getIsCritical() === true))) {
			writeToFile(PathProvider::logCriticalPhpException(), $message . "\n");
		}

		if (($exception instanceof \Error)) {
			writeToFile(PathProvider::logErrorPhp(), $message . "\n");
		}
	}

	/**
	 * Отдаем ответ с ошибкой
	 *
	 * @param int $http_code
	 *
	 * @return void
	 */
	public static function showError(int $http_code):void {

		// если работаем из консоли
		if (isCLi()) {
			return;
		}

		// добавляем response_code к ответу
		http_response_code($http_code);

		showAjax([]);
	}

	/**
	 * Закрываем все соединения
	 *
	 * @return void
	 */
	public static function doShardingEnd():void {

		// закрываем соединения
		try {
			@\sharding::end();
		} catch (\Exception) {
			// nothing
		};
	}

	/**
	 * Формирует строку с сообщением об ошибке
	 *
	 * @param object $exception
	 * @param int    $code
	 *
	 * @return string
	 */
	protected static function _getMessageAsString(object $exception, int $code):string {

		$class_name  = get_class($exception);
		$date        = date(DATE_FORMAT_FULL_S);
		$err_message = $exception->getMessage();
		$line        = $exception->getLine();
		$file        = $exception->getFile();

		if (!method_exists($exception, "getModule")) {

			$module = "";

			// узнаем, в каком модуле произошла ошибка
			$module_string = explode("src/Compass/", $file);

			// проверяем, что это действительно сабмодуль и получаем его название
			if (count($module_string) > 1) {
				$module = explode("/", $module_string[1])[0];
			}
		} else {
			$module = $exception->getModule();
		}

		$message = "[$module]" . PHP_EOL;

		if (!$module) {
			$message = "";
		}
		return $message . "[$class_name], code {$code}, $date\n$err_message\n{$file} on line {$line}";
	}

	/**
	 * Форматируем trace под наши нужды
	 *
	 * @param object $exception
	 *
	 * @return string
	 */
	protected static function _getTraceAsString(object $exception):string {

		$rtn   = "";
		$count = 0;
		foreach (array_reverse($exception->getTrace()) as $frame) {

			$is_index = isset($frame["args"]) && isset($frame["file"]) && (str_contains($frame["file"], "index.php"));

			// если файл индексовый, не скрываем аргументы, чтобы понять, что за запрос к нам пришел
			if (!$is_index && !ErrorProvider::display()) {
				$args = self::_replaceFunctionArgumentsWithTypes($frame["args"]);
			} else {
				$args = self::_addTypeToFunctionArguments($frame["args"] ?? []);
			}

			$rtn .= sprintf("#%s %s(%s): %s(%s)" . PHP_EOL, $count, isset($frame["file"]) ? $frame["file"] : "unknown file", isset($frame["line"]) ? $frame["line"] : "unknown line", (isset($frame["class"])) ? $frame["class"] . $frame["type"] . $frame["function"] : $frame["function"], join(", ", $args));
			$count++;
		}

		$rtn = str_replace(PathProvider::root(), "/", $rtn);
		return $rtn;
	}

	/**
	 * Подменяем аргументы функции типами
	 * Логика следующая - в логах для паблика не должно быть никакой конфиденциальной информации.
	 * Поэтому прячем все, что можем.
	 * Исключения - мапы, числа и булевы значения
	 *
	 * @param array $frame
	 *
	 * @return array
	 * @long switch...case со всеми возможными типами, что могут попасться, и которые нужно правильно обработать
	 */
	protected static function _replaceFunctionArgumentsWithTypes(array $frame_args):array {

		$args = [];
		foreach ($frame_args as $arg) {

			$type = gettype($arg);
			switch ($type) {

				case "string":
					if (self::_checkIsMap($arg)) {

						$args[] = "'" . $arg . "'";
						break;
					}
					$args[] = "string" . "<length:" . mb_strlen($arg) . ">";
					break;
				case "array":
					$args[] = "Array" . "<length:" . count($arg) . ">";
					break;
				case "object":
					$args[] = get_class($arg);
					break;
				case "resource":
					$args[] = get_resource_type($arg);
					break;
				case "integer":
				case "boolean":
					$args[] = $arg;
					break;
				default:
					$args[] = $type;
					break;
			}
		}

		return $args;
	}

	/**
	 * Форматируем аргументы
	 *
	 * @param array $frame_args
	 *
	 * @return array
	 */
	protected static function _addTypeToFunctionArguments(array $frame_args):array {

		$args = [];
		foreach ($frame_args as $arg) {

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

		return $args;
	}

	/**
	 * Проверяем, является ли строка мапой
	 *
	 * @param string $string
	 *
	 * @return bool
	 */
	protected static function _checkIsMap(string $string):bool {


		// проверяем, строка является json или нет (ну и получаем массив сразу, если да)
		$json = fromJson($string);

		if (count($json) < 1) {
			return false;
		}

		// если в json нет поле _ и ?, то перед нами не мапа
		if (!isset($json["_"]) || !isset($json["?"])) {
			return false;
		}

		// это мапа
		return true;
	}
}