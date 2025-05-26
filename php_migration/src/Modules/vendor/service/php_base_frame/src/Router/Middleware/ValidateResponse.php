<?php

namespace BaseFrame\Router\Middleware;

use BaseFrame\Exception\Domain\ReturnFatalException;
use BaseFrame\Router\Request;

/**
 *
 */
class ValidateResponse implements Main {

	/**
	 * валидируем ответ
	 */
	public static function handle(Request $request):Request {

		// если какой-то левак пришел в ответе (например забыли вернуть return $this->ok)
		if (!isset($request->response["status"])) {
			throw new ReturnFatalException("Final api response do not have status field.");
		}

		return $request;
	}
}