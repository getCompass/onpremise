<?php

namespace Compass\Premise;

use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Handler\Api;

/**
 * ЗАДАЧА КЛАССА связывать между собой проекты и их взаимодействие
 * внутренне безопасное сервсиное общение между микросервисами
 */
class Socket_Handler extends Api implements \RouteHandler {

	public const ALLOW_CONTROLLERS = [
		"event",
		"task",
		"premise",
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
			"namespace" => __NAMESPACE__,
			"api_type"  => "socket",
			"socket"    => [
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

		// выполняем все мидлвары по порядку
		$response = $router->handler($route, $post_data, $extra);

		// закрываем соедиенения, если запускали не из консоли
		self::_closeConnectionsIfRunFromNotCli();

		return $response;
	}

	// выбрасываем ошибку, если контроллер недоступен
	protected static function _throwIfControllerIsNotAllowed(string $controller):void {

		// приводим все контроллеры из списка к нижнему регистру
		$allow_controllers = array_map("strtolower", self::ALLOW_CONTROLLERS);

		// проверяем доступные контролеры
		if (!in_array($controller, $allow_controllers)) {
			throw new ControllerMethodNotFoundException("CONTROLLER is not allowed. Check Handler allow controller.");
		}
	}

	// закрываем соединения, если запускали не из консоли
	protected function _closeConnectionsIfRunFromNotCli():void {

		// если запуск был не из консоли - закрываем соединения
		if (!isCLi()) {
			\sharding::end();
		}
	}
}
