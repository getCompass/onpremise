<?php declare(strict_types=1);

namespace Application\Entrypoint;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Обработчик для запросов на /janus точку входа.
 * Перенаправляет данные в Janus из приложения.
 */
class Janus {

	/**
	 * Точка входа для запроса на endpoint /janus.
	 *
	 * @throws ControllerMethodNotFoundException
	 * @throws EndpointAccessDeniedException
	 */
	public static function processRequest(string $method, array $post):array {

		if ($method !== "get" && $method !== "post") {
			throw new EndpointAccessDeniedException("only get and post methods allowed");
		}

		// получаем обработчик для указанного пути
		$handler = Resolver::instance()->resolveHandlerByRoute("janus", $method);
		return $handler->handle($method, $post);
	}
}
