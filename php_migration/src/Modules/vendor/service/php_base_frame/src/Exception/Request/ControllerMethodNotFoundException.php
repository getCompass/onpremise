<?php

namespace BaseFrame\Exception\Request;

use BaseFrame\Exception\RequestException;

/**
 * Не найден метод контроллера
 */
class ControllerMethodNotFoundException extends RequestException {

	const HTTP_CODE  = 404;
}