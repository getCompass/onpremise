<?php

namespace BaseFrame\Exception;

/**
 *  Исключение уровня запроса
 */
class RequestException extends BaseException {

	const HTTP_CODE   = 500; // http-код, который нужно отдать в ответе при появлении исключения
	const IS_CRITICAL = false; // критическая ли ошибка
}