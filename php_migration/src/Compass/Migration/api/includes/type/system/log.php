<?php

namespace Compass\Migration;

/**
 * Класс для консольного лога удобного
 */
class Type_System_Log {

	/**
	 * info логи
	 *
	 * @param string $text
	 *
	 * @throws paramException
	 */
	public static function doInfoLog(string $text):void {

		console(self::_getPrefix() . blueText($text));
	}

	/**
	 * дебаг логи
	 *
	 * @param mixed $arg
	 *
	 * @throws paramException
	 */
	public static function doDebugLog(mixed $arg):void {

		console(self::_getPrefix(), $arg);
	}

	/**
	 * error логи
	 *
	 * @param string $text
	 *
	 * @throws paramException
	 */
	public static function doErrorLog(string $text):void {

		console(self::_getPrefix() . redText($text));
	}

	/**
	 * успешные логи
	 *
	 * @param string $text
	 *
	 * @throws paramException
	 */
	public static function doCompleteLog(string $text):void {

		console(self::_getPrefix() . greenText($text));
	}

	/**
	 * префикс
	 *
	 * @return string
	 */
	protected static function _getPrefix():string {

		$mysql_host = $GLOBALS["MYSQL_HOST"] ?? "";
		if (mb_strlen($mysql_host) > 0) {

			$mysql_port = $GLOBALS["MYSQL_PORT"] ?? "";
			return "[" . date("h:i:s") . " host: " . $mysql_host . ":" . $mysql_port . "] ";
		}
		return "[" . date("h:i:s") . "] ";
	}
}