<?php declare(strict_types=1);

namespace Application\Entrypoint;

use BaseFrame\Exception\Domain\ParseFatalException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\EndpointAccessDeniedException;

/**
 * Обработчик для запросов на /proxy точку входа.
 * Как правило обрабатывает данные, которые нужно передать от медиа-сервера в приложение.
 */
class Proxy {

	/**
	 * Точка входа для запроса на endpoint /proxy.
	 * @throws ControllerMethodNotFoundException
	 */
	public static function processRequest():array {

		// получаем обработчик для указанного пути
		$handler = Resolver::instance()->resolveHandlerByRoute("proxy", "/");
		return $handler->handle("", []);
	}
}
