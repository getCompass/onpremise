<?php declare(strict_types = 1);

namespace Application\Entrypoint;

use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Обработчик для integration-запросов.
 */
class Integration {

	/**
	 * Точка входа для запроса на endpoint integration.
	 *
	 * @throws ControllerMethodNotFoundException
	 * @throws EndpointAccessDeniedException
	 */
	public static function processRequest(string $module_name, string $method_name, string $module, array $post):array {

		if ($method_name === "") {
			throw new EndpointAccessDeniedException("No method provided");
		}

		$exploded = explode(".", $method_name, -1);
		$route    = $module . "." . implode(".", $exploded);

		// получаем обработчик для указанного пути
		$handler = Resolver::instance()->resolveHandlerByRoute($module_name, "integration", $route);

		return $handler->handle($method_name, $post);
	}
}
