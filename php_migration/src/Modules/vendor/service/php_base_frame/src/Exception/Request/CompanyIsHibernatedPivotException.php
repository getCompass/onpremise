<?php

namespace BaseFrame\Exception\Request;

use BaseFrame\Exception\RequestException;

/**
 * Компания недоступна
 */
class CompanyIsHibernatedPivotException extends RequestException {

	const HTTP_CODE = 490;
}