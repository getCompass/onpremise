<?php declare(strict_types = 1);

namespace Application\Entrypoint;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Обработчик для socket-запросов.
 */
class Socket {

	/**
	 * Точка входа для запроса на endpoint socket.
	 *
	 * @throws ControllerMethodNotFoundException
	 * @throws EndpointAccessDeniedException
	 * @throws ParseFatalException
	 */
	public static function processRequest(string $module_name, string $method_name, string $module, array $post, bool $is_local):array {

		if ($method_name === "") {
			throw new EndpointAccessDeniedException("No method provided");
		}

		$exploded = explode(".", $method_name, -1);
		$route    = $module . "." . implode(".", $exploded);

		if (isset($post["is_local"])) {
			throw new ParseFatalException("unsupported field is_local");
		}

		$post["is_local"] = $is_local;

		// получаем обработчик для указанного пути
		$handler = Resolver::instance()->resolveHandlerByRoute($module_name, "socket", $route);

		return $handler->handle($method_name, $post);
	}
}
