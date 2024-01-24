<?php

namespace Compass\FileBalancer;

/**
 * ЗАДАЧА КЛАССА связывать между собой проекты и их взаимодействие
 * внутренне безопасное сервсиное общение между микросервисами
 */
class Socket_Handler implements \RouteHandler {

	public const ALLOW_CONTROLLERS = [
		"files",
		"stat",
		"admin",
		"relocate",
		"system",
		"previews",
		"tests",
		"antispam",
	];

	/**
	 * Возвращает обслуживаемые методы.
	 * @return string[]
	 */
	public function getServedRoutes():array {

		return array_map(static fn(string $controller) => "files" . "." . str_replace("_", ".", $controller), static::ALLOW_CONTROLLERS);
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
			],
		];

		// здесь описана последовательность проверок
		$middleware_list = [
			\BaseFrame\Router\Middleware\ValidateRequest::class,
			\BaseFrame\Router\Middleware\SocketAuthorization::class,
			\BaseFrame\Router\Middleware\ModifyHandler::class,
			\BaseFrame\Router\Middleware\InitializeController::class,
			\BaseFrame\Router\Middleware\Run::class,
			\BaseFrame\Router\Middleware\ValidateResponse::class,
		];

		// добавляем проверку на статус компании (спит, активная иил переехала)
		if (CURRENT_SERVER === CLOUD_SERVER) {
			$middleware_list = array_merge([\CompassApp\Controller\Middleware\CheckCompany::class, Middleware_InitializeConf::class], $middleware_list);
		}

		// выполняем все мидлвары по очереди
		$router = new \BaseFrame\Router\Middleware($middleware_list);

		$response = $router->handler($route, $post, $extra);

		// если запуск был не из консоли - закрываем соединения
		if (!isCLi()) {
			\sharding::end();
		}

		return $response;
	}
}