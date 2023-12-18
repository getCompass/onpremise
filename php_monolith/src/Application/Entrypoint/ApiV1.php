<?php declare(strict_types = 1);

namespace Application\Entrypoint;

use BaseFrame\Exception\Request\EndpointAccessDeniedException;

class ApiV1 {

	/**
	 * Точка входа для запроса на endpoint api версии 1.
	 */
	public static function processRequest(string $module_name, string $method_name, array $post, int $user_id = 0):array {

		if ($method_name === "") {
			throw new EndpointAccessDeniedException("No method provided");
		}

		$exploded = explode(".", $method_name, -1);
		$route    = implode(".", $exploded);

		// получаем обработчик для указанного пути
		$handler = Resolver::instance()->resolveHandlerByRoute($module_name, "apiv1", $route);

		return $handler->handle($method_name, $post, $user_id);
	}
}
