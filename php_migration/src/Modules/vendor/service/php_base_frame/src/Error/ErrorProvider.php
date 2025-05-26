<?php

namespace BaseFrame\Error;

/**
 * Класс-обертка для работы с ошибками
 */
class ErrorProvider {

	/**
	 * Закрываем конструктор.
	 */
	protected function __construct() {

	}

	/**
	 * получаем is_display_error
	 *
	 */
	public static function display():bool {

		return ErrorHandler::instance()->display();
	}
}
