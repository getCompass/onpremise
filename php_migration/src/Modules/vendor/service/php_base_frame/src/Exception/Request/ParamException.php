<?php

namespace BaseFrame\Exception\Request;

use BaseFrame\Exception\RequestException;

/**
 * Ошибка вводимых данных
 */
class ParamException extends RequestException {

	const HTTP_CODE  = 400;
}