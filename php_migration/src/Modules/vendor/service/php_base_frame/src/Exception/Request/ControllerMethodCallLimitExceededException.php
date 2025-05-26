<?php

namespace BaseFrame\Exception\Request;

use BaseFrame\Exception\RequestException;

/**
 * Превышено количество вызовов метода контроллера
 */
class ControllerMethodCallLimitExceededException extends RequestException {

	const HTTP_CODE  = 423;
}