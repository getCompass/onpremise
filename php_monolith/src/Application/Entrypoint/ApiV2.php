<?php declare(strict_types = 1);

namespace Application\Entrypoint;

use BaseFrame\Exception\Request\EndpointAccessDeniedException;

class ApiV2 {

	/**
	 * Точка входа для запроса на endpoint api версии 2.
	 */
	public static function processRequest(string $module_name, string $method_name, array $post, int $user_id = 0):array {

		if ($method_name === "") {
			throw new EndpointAccessDeniedException("No method provided");
		}

		$exploded = explode(".", $method_name, -1);
		$route    = implode(".", $exploded);

		if ($module_name == "Userbot") {
			$post["payload"] = json_encode($post);
		}

		// получаем обработчик для указанного пути
		$handler = Resolver::instance()->resolveHandlerByRoute($module_name, "apiv2", $route);

		return $handler->handle($method_name, $post, $user_id);
	}
}
