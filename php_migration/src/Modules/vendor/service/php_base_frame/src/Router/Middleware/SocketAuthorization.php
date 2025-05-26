<?php

namespace BaseFrame\Router\Middleware;

use BaseFrame\Exception\Request\EndpointAccessDeniedException;
use BaseFrame\Router\Request;

/**
 * мидлware для авторизации сокетов
 */
class SocketAuthorization implements Main {

	/**
	 * поверяем безопасность данных в запросе
	 */
	public static function handle(Request $request):Request {

		// устанавливаем параметры запроса
		$sender_module = $request->post_data["sender_module"];
		$allow_conf    = $request->extra["socket"]["allow_config"];

		// проверяем есть ли конфиг для нашего модуля
		if (!isset($allow_conf[$sender_module])) {
			throw new EndpointAccessDeniedException("Sender module doesn't have access to current module");
		}

		$module_config = $allow_conf[$sender_module];
		$allow_methods = self::_getAllowMethods($module_config);

		$is_local    = $request->post_data["is_local"];
		$json_params = $request->post_data["json_params"];
		$public_key  = $module_config["auth_key"];

		$request->extra["socket"]["sender_module"] = $request->post_data["sender_module"];
		$request->extra["socket"]["company_id"]    = $request->post_data["company_id"] ?? 0;

		if ($module_config["auth_type"] === \BaseFrame\Socket\Authorization\Handler::AUTH_TYPE_SSL) {
			$public_key = $request->extra["socket"]["public_key"];
		}

		$request->user_id = $request->post_data["user_id"];

		// проверяем можно ли дергать метод
		if (!in_array(strtolower($request->post_data["method"]), $allow_methods)) {
			throw new EndpointAccessDeniedException("User not authorized for this actions.");
		}

		// сверяем подпись
		if (!\BaseFrame\Socket\Authorization\Handler::isSignatureAgreed($module_config["auth_type"], $request->post_data["signature"], $json_params, $public_key, $is_local)) {
			throw new EndpointAccessDeniedException("User not authorized for this actions.");
		}

		return $request;
	}

	// получаем, что метод в списке доверенных
	protected static function _getAllowMethods(array $module_conf):array {

		// берем глобальный конфиг
		$allow_methods = $module_conf["allow_methods"];

		// приводим методы к нижнему регистру
		$allow_methods = array_change_key_case($allow_methods, CASE_LOWER);

		// приводим методы к нижнему регистру
		return array_map("strtolower", $allow_methods);
	}
}