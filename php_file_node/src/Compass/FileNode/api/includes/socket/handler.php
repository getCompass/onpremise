<?php

namespace Compass\FileNode;

use BaseFrame\Handler\Api;

/**
 * ЗАДАЧА КЛАССА связывать между собой проекты и их взаимодействие
 * внутренне безопасное сервсиное общение между микросервисами
 */
class Socket_Handler extends Api implements \RouteHandler {

	public const ALLOW_CONTROLLERS = [
		"nodes",
		"previews",
		"system",
		"intercom",
		"tests",
	];

	/**
	 * @inheritDoc
	 */
	public function getServedRoutes():array {

		return array_map(static fn(string $controller) => CURRENT_MODULE . "." . str_replace("_", ".", $controller), static::ALLOW_CONTROLLERS);
	}

	/**
	 * @inheritDoc
	 */
	public function getType():string {

		return "socket";
	}

	/**
	 * @inheritDoc
	 */
	public function __toString():string {

		return static::class;
	}

	/**
	 * @inheritDoc
	 */
	public function handle(string $route, array $post_data, int $user_id = 0):array {

		$extra = [
			"namespace"  => __NAMESPACE__,
			"api_type"   => $this->getType(),
			"socket"     => [
				"allow_config" => getConfig("SOCKET_ALLOW_KEY"),
			],
		];

		// здесь описана последовательность проверок
		$router = new \BaseFrame\Router\Middleware([
			\BaseFrame\Router\Middleware\ValidateRequest::class,
			\BaseFrame\Router\Middleware\SocketAuthorization::class,
			\BaseFrame\Router\Middleware\ModifyHandler::class,
			\BaseFrame\Router\Middleware\InitializeController::class,
			\BaseFrame\Router\Middleware\SetMethodVersion::class,
			\BaseFrame\Router\Middleware\Run::class,
			\BaseFrame\Router\Middleware\ValidateResponse::class,
		]);

		$response = $router->handler($route, $post_data, $extra);

		// если запуск был не из консоли - закрываем соединения
		$this->_closeConnectionsIfRunFromNotCli();

		return $response;
	}
}
