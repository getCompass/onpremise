<?php

namespace BaseFrame\Router\Middleware;

use BaseFrame\Router\Request;

/**
 * Добавляет auth data в ответ. Auth data нужна для передачи клиенту токена авторизации запросов,
 * который будет передаваться в заголовке Authorization. По своей логике работы дублирует Action,
 * но Action требует ручного взаимодействия, а для авторизации не всегда можно предугадать,
 * в какой момент произойдет изменение авторизационных данных.
 */
class AttachAuthData implements Main {

	/**
	 * При необходимости добавляет данные авторизации в action список ответа.
	 */
	public static function handle(Request $request):Request {

		$auth_data = \BaseFrame\Http\Authorization\Data::inst();

		// если данные авторизации не менялись, то ничего не делаем
		if (!$auth_data->hasChanges()) {
			return $request;
		}

		$request->response["actions"][] = [
			"type" => "authorization",
			"data" => $auth_data->get()
		];

		return $request;
	}
}
