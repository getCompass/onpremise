<?php

namespace BaseFrame\Router\Middleware;
use BaseFrame\Router\Request;

/**
 * Основной класс от которого реализуются все middleware
 */
interface Main {

	/**
	 */
	public static function handle(Request $request):Request;
}