<?php

namespace Compass\Speaker;

/**
 * ЗАДАЧА КЛАССА связывать между собой проекты и их взаимодействие
 * внутренне безопасное сервсиное общение между микросервисами
 */
class Socket_Handler implements \RouteHandler {

	public const ALLOW_CONTROLLERS = [
		"global",
		"calls",
		"system",
		"tests",
		"analytics",
		"reports",
		"event",
		"systemevent",
		"antispam",
		"task",
	];

	/**
	 * Возвращает обслуживаемые методы.
	 * @return string[]
	 */
	public function getServedRoutes():array {

		$output = [];
		foreach (static::ALLOW_CONTROLLERS as $controller) {
			$output[] = CURRENT_MODULE . "." . $controller;
		}
		return $output;
	}

	/**
	 * Возвращает тип обработчика.
	 * @return string[]
	 */
	public function getType():string {

		return "socket";
	}

	/**
	 * ToString конвертация.
	 * @return string
	 */
	public function __toString():string {

		return static::class;
	}

	// в качестве параметров принимает:
	//	@$api_method 	название метода вида test.code401
	//	@$post_data 	параметры post запроса которые будут использоваться внутри контролеров
	//	@user_id	для отладки и тестов. действуйет только в режиме cli
	// @long
	public function handle(string $route, array $post):array {

		$extra = [
			"namespace" => __NAMESPACE__,
			"api_type"  => $this->getType(),
			"socket"    => [
				"allow_config" => getConfig("SOCKET_ALLOW_KEY"),
				"public_key"   => PIVOT_TO_COMPANY_PUBLIC_KEY,
			],
		];

		// здесь описана последовательность проверок
		$router = new \BaseFrame\Router\Middleware([
			\CompassApp\Controller\Middleware\CheckCompany::class,
			Middleware_InitializeConf::class,
			\BaseFrame\Router\Middleware\ValidateRequest::class,
			\BaseFrame\Router\Middleware\SocketAuthorization::class,
			\BaseFrame\Router\Middleware\ModifyHandler::class,
			\BaseFrame\Router\Middleware\InitializeController::class,
			\BaseFrame\Router\Middleware\Run::class,
			\BaseFrame\Router\Middleware\ValidateResponse::class,
		]);

		$response = $router->handler($route, $post, $extra);

		// отсылаем данные в коллектор
		\BaseFrame\Monitor\Core::flush();

		// если запуск был не из консоли - закрываем соединения
		if (!isCLi()) {
			\sharding::end();
		}

		return $response;
	}
}