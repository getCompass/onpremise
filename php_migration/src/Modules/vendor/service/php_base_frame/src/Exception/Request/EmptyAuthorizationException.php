<?php

namespace BaseFrame\Exception\Request;

use BaseFrame\Exception\RequestException;

/**
 * Неверные данные авторизации.
 */
class EmptyAuthorizationException extends RequestException {

	const HTTP_CODE = 401;
}