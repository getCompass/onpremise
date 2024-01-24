<?php declare(strict_types = 1);

namespace Application\Entrypoint;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Socket
 */
class Socket {

	/**
	 * Точка входа для запроса на endpoint socket.
	 */
	public static function processRequest(string $method_name, string $module, array $post, bool $is_local):array {

		if ($method_name === "") {
			throw new EndpointAccessDeniedException("No method provided");
		}

		$exploded = explode(".", $method_name, -1);
		$route    = "file_node" . "." . implode(".", $exploded);

		if (isset($post["is_local"])) {
			throw new ParseFatalException("unknown field is_local");
		}
		$post["is_local"] = $is_local;

		// получаем обработчик для указанного пути
		$handler = Resolver::instance()->resolveHandlerByRoute("socket", $route);

		return $handler->handle($method_name, $post);
	}
}
