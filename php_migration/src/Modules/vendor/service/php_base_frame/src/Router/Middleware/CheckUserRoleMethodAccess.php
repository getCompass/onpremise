<?php

namespace BaseFrame\Router\Middleware;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\CaseException;

/**
 * Мидлвар, который проверяет наличие ограничения к вызываемому методу по роли пользователя в пространстве
 */
class CheckUserRoleMethodAccess implements Main {

	/**
	 * Обработчик
	 *
	 * @return \BaseFrame\Router\Request
	 * @throws CaseException
	 * @throws ParseFatalException
	 */
	public static function handle(\BaseFrame\Router\Request $request):\BaseFrame\Router\Request {

		// если не определена роль пользователя в пространстве
		if (!isset($request->extra["user"]["role"])) {

			// если упало это исключение, то в реквесте нет информации о роли, это бывает в случаях:
			// – мидлвар вызывается слишком рано в цепочке
			// – информации о роли впринципе нет в реквесте (например в модуле php_pivot)
			throw new ParseFatalException("unexpected usage");
		}

		// если не задали error_code ошибки action not allowed
		if (!isset($request->extra["action_not_allowed_error_code"])) {
			throw new ParseFatalException("api handler action_not_allowed_error_code not defined");
		}

		$role = $request->extra["user"]["role"];

		// список запрещенных методов по ролям
		$restricted_method_list_by_role = $request->controller_class::RESTRICTED_METHOD_LIST_BY_ROLE;

		// если для такой роли ничего не запрещено, то ничего не делаем
		if (!isset($restricted_method_list_by_role[$role])) {
			return $request;
		}

		// переводим в нижний регистр все запрещенные методы
		$restricted_methods = array_map(static fn(string $method) => mb_strtolower($method),
			$restricted_method_list_by_role[$role]);

		// если метод запрещен
		if (in_array($request->method_name, $restricted_methods)) {
			throw new CaseException($request->extra["action_not_allowed_error_code"], "action not allowed");
		}

		return $request;
	}
}