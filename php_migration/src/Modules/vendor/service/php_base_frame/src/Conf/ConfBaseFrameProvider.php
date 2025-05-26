<?php

namespace BaseFrame\Conf;

/**
 * Класс-обертка для работы с конфигами base_frame
 */
class ConfBaseFrameProvider {

	/**
	 * Закрываем конструктор.
	 */
	protected function __construct() {

	}

	/**
	 * получаем max_execution_time
	 *
	 */
	public static function setHeaderList(array $header_list):void {

		ConfBaseFrameHandler::instance()->setHeaderList($header_list);
	}

	/**
	 * получаем max_execution_time
	 *
	 */
	public static function maxExecutionTime():int {

		return ConfBaseFrameHandler::instance()->maxExecutionTime();
	}

	/**
	 * получаем web_charset
	 *
	 */
	public static function webCharset():string {

		return ConfBaseFrameHandler::instance()->webCharset();
	}

	/**
	 * получаем sql_charset
	 *
	 */
	public static function sqlCharset():string {

		return ConfBaseFrameHandler::instance()->sqlCharset();
	}
}
