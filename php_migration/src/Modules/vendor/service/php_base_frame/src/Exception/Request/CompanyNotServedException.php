<?php

namespace BaseFrame\Exception\Request;

use BaseFrame\Exception\RequestException;

/**
 * Компания не обслуживается данным сервисом.
 */
class CompanyNotServedException extends RequestException {

	const HTTP_CODE  = 404;
}