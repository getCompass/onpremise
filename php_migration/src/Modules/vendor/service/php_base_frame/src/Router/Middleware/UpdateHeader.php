<?php

namespace BaseFrame\Router\Middleware;

use BaseFrame\Router\Request;

/**
 * Добавляет в ответ action с изменением заголовка.
 */
class UpdateHeader implements Main {

	/**
	 * При необходимости добавляет данные авторизации в action список ответа.
	 */
	public static function handle(Request $request):Request {

		$auth_data = \BaseFrame\Http\AnswerAction\SetHeader::inst();

		// если данные авторизации не менялись, то ничего не делаем
		if (!$auth_data->hasChanges()) {
			return $request;
		}

		$request->response["actions"][] = [
			"type" => "headers",
			"data" => $auth_data->get()
		];

		return $request;
	}
}
