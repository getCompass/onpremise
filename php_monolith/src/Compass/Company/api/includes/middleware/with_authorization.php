<?php

namespace Compass\Company;

use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Router\Request;

/**
 * Проверка авторизации
 */
class Middleware_WithAuthorization implements \BaseFrame\Router\Middleware\Main {

	/**
	 * @param Request $request
	 *
	 * @return Request
	 * @throws EndpointAccessDeniedException
	 * @throws \busException
	 */
	public static function handle(Request $request):Request {

		try {

			// проверяем сессию
			[$user_id, $session_uniq, $role, $permissions, $session_extra, $user_disabled_analytics_event_group_list] =
				Type_Session_Main::getSession($request->extra["company_cache_class"]);
		} catch (\cs_SessionNotFound) {

			// если не нашли сессию в базах то она скорее всего не активна больше
			throw new EndpointAccessDeniedException("company_user_session not found");
		}

		if ($user_id == 0) {

			if (!isset($request->extra["allowed_without_controller"]) || !in_array($request->controller_name, $request->extra["not_auth_controller_list"])) {
				throw new EndpointAccessDeniedException("User not authorized for this actions.");
			}
		}

		$request->user_id                                                   = $user_id;
		$request->extra["user"]["session_uniq"]                             = $session_uniq;
		$request->extra["user"]["role"]                                     = $role;
		$request->extra["user"]["permissions"]                              = $permissions;
		$request->extra["user"]["user_disabled_analytics_event_group_list"] = $user_disabled_analytics_event_group_list;
		$request->extra["user"]["need_block_if_premium_inactive"]           = (bool) ($session_extra["extra"]["need_block_if_premium_inactive"] ?? false);
		$request->extra["user"]["premium_active_till"]                      = $session_extra["extra"]["premium_active_till"] ?? 0;

		return $request;
	}
}