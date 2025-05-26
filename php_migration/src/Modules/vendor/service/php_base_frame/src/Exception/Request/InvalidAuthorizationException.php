<?php

namespace BaseFrame\Exception\Request;

use BaseFrame\Exception\RequestException;

/**
 * Неверные данные авторизации.
 */
class InvalidAuthorizationException extends RequestException {

	const HTTP_CODE = 403;
}