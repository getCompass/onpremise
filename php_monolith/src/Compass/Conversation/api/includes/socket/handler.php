<?php

namespace Compass\Conversation;

/**
 * ЗАДАЧА КЛАССА связывать между собой проекты и их взаимодействие
 * внутренне безопасное сервсиное общение между микросервисами
 */
class Socket_Handler implements \RouteHandler {

	public const ALLOW_CONTROLLERS = [
		"messages",
		"conversations",
		"groups",
		"system",
		"talking",
		"systemevent",
		"invites",
		"event",
		"antispam",
		"task",
		"userbot",
		"intercom",
		"file",
		"search",
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

		// инициализируем технический мониторинг;
		// вообще это делать нужно не здесь, а где-то выше по стеку,
		// но в силу того, что подключения к сервисам
		// закрываются тут, то и объявляем все это дело тут
		\BaseFrame\Monitor\Core::init(new Type_Monitor_SenderLogMetric("text-metric-socket"), false, true, false);
		\BaseFrame\Monitor\Core::getMetricAggregator()->setDefaultLabel("module", "php_" . CURRENT_MODULE);
		\BaseFrame\Monitor\Core::getLogAggregator()
			->setDefaultLabel("module", "php_" . CURRENT_MODULE)
			->setLogLevel(\BaseFrame\Monitor\LogAggregator::LOG_LEVEL_INFO);

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