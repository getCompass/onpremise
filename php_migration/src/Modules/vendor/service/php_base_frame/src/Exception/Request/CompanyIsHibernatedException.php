<?php

namespace BaseFrame\Exception\Request;

use BaseFrame\Exception\RequestException;

/**
 * Компания недоступна
 */
class CompanyIsHibernatedException extends RequestException {

	const HTTP_CODE = 503;
}