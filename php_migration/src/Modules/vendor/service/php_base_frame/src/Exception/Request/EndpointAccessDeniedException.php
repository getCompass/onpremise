<?php

namespace BaseFrame\Exception\Request;

use BaseFrame\Exception\RequestException;

/**
 * Ошибка доступа к точке входа
 */
class EndpointAccessDeniedException extends RequestException {

	const HTTP_CODE  = 401;
}