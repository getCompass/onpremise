<?php

namespace BaseFrame\Exception\Request;

use BaseFrame\Exception\RequestException;

/**
 * Не найден конфиг для компании.
 */
class CompanyConfigNotFoundException extends RequestException {

	const HTTP_CODE = 500;
}