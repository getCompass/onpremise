<?php

namespace BaseFrame\Exception\Request;

use BaseFrame\Exception\RequestException;

/**
 * Компания ушла и не обещала вернуться
 */
class CompanyIsRelocatingPivotException extends RequestException {

	const HTTP_CODE = 491;
}