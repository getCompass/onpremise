<?php

namespace Compass\FileBalancer;

use BaseFrame\Router\Request;

/**
 *
 */
class Middleware_WithoutAuthorization implements \BaseFrame\Router\Middleware\Main {

	/**
	 * Функция — обработчик запроса.
	 * Должна обрабатывать входные данные для обработчика запросов.
	 *
	 * Возвращает массив, который затем будет закодирован в json для возврата.
	 */
	public static function handle(Request $request):Request {

		$request->user_id = $request->extra["need_user_id"];

		$request->extra["user"]["session_uniq"] = "";

		if (CURRENT_SERVER == CLOUD_SERVER) {

			$member                                = Gateway_Bus_CompanyCache::getMember($request->user_id);
			$request->extra["user"]["role"]        = $member->role;
			$request->extra["user"]["permissions"] = $member->permissions;
		}

		return $request;
	}
}