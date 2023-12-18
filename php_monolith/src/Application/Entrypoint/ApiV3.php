<?php declare(strict_types = 1);

namespace Application\Entrypoint;

use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Обработчик запросов точки входа ApiV3.
 */
class ApiV3 {

	/**
	 * Точка входа для запроса на endpoint api версии 3.
	 *
	 * @throws ControllerMethodNotFoundException
	 * @throws EndpointAccessDeniedException
	 */
	public static function processRequest(string $module_name, string $method_name, string $payload):array {

		if ($method_name === "") {
			throw new EndpointAccessDeniedException("No method provided");
		}

		$exploded             = explode(".", $method_name, -1);
		$route                = implode(".", $exploded);
		$post_data["payload"] = $payload;

		// получаем обработчик для указанного пути
		$handler = Resolver::instance()->resolveHandlerByRoute($module_name, "apiv3", $route);
		return $handler->handle($method_name, $post_data);
	}
}
