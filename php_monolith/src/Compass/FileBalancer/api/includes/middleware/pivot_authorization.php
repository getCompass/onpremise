<?php

namespace Compass\FileBalancer;

use BaseFrame\Router\Request;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Exception\Request\InvalidAuthorizationException;
use BaseFrame\Exception\Request\EmptyAuthorizationException;

/**
 * Авторизация
 */
class Middleware_PivotAuthorization implements \BaseFrame\Router\Middleware\Main {

	/**
	 * Авторизуем пользователя
	 * @throws
	 */
	public static function handle(Request $request):Request {

		try {

			$user_id      = Type_Session_Main::getUserIdBySession();
			$session_uniq = Type_Session_Main::getSessionUniqBySession();
		} catch (InvalidAuthorizationException | EmptyAuthorizationException) {

			// если не нашли сессию в базах то она скорее всего не активна больше
			// если куки пусты или сессия не валидна просим получить сессию
			throw new cs_AnswerCommand("need_call_start", []);
		}

		if ($user_id === 0) {

			if (!isset($request->extra["allowed_without_controller"]) || !in_array($request->controller_name, $request->extra["not_auth_controller_list"])) {
				static::_rejectRequest();
			}
		}

		$request->user_id                       = $user_id;
		$request->extra["user"]["session_uniq"] = $session_uniq;

		return $request;
	}

	/**
	 * Возвращает клиенту в ответе ожидаемые им данные.
	 * @throws \BaseFrame\Exception\Request\EndpointAccessDeniedException
	 * @throws \Compass\FileBalancer\cs_AnswerCommand
	 */
	public static function _rejectRequest():void {

		// если клиент ожидает 401, то возвращаем ему 401
		if (\BaseFrame\Http\Header\AuthorizationControl::parse()::expect401()) {
			throw new EndpointAccessDeniedException("User not authorized for this actions.");
		}

		// иногда клиенты не хотят 401 и им нужно запустить
		// весь флоу валидации сессии через start + doStart
		throw new cs_AnswerCommand("need_call_start", []);
	}
}