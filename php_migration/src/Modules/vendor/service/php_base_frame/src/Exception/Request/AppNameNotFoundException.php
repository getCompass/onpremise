<?php

namespace BaseFrame\Exception\Request;

use BaseFrame\Exception\RequestException;

/**
 * Приложение клиента не найдено
 */
class AppNameNotFoundException extends RequestException {

	const HTTP_CODE = 400;

}