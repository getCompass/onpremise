<?php

namespace BaseFrame\Exception\Request;

use BaseFrame\Exception\RequestException;

/**
 * Платформа клиента не найдена
 */
class PlatformNotFoundException extends RequestException {

	const HTTP_CODE = 400;

}