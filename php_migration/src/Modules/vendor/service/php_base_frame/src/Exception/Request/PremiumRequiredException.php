<?php

namespace BaseFrame\Exception\Request;

use BaseFrame\Exception\RequestException;

/**
 * Исключение — для доступа к методу необходим действующий премиум.
 */
class PremiumRequiredException extends RequestException {

	public const HTTP_CODE = 402;
}
