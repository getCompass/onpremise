<?php declare(strict_types = 1);

namespace Application\Entrypoint;

use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Userbot
 */
class Userbot {

	/**
	 * Точка входа для запроса на endpoint api userbot.
	 */
	public static function processRequest(string $method_name, array $post, int $user_id = 0):array {

		if ($method_name === "") {
			throw new EndpointAccessDeniedException("No method provided");
		}

		$exploded = explode(".", $method_name, -1);
		$route    = implode(".", $exploded);

		// получаем обработчик для указанного пути
		$handler = Resolver::instance()->resolveHandlerByRoute("userbot", $route);
		
		return $handler->handle($method_name, $post, $user_id);
	}
}
