<?php

namespace Compass\FileNode;

use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Handler\Api;

/**
 * Примечания:
 * 1. все запросы к API методом POST
 * 2. все ответы в формате JSON
 * 3. у всех ответов есть поле status (ok||error) && response
 * 4. кодировка UTF-8
 * 5. все ошибки задокументированы в апи документаторе для каждого метода
 * 6. ошибки и исклчюения отрабатываются сервер HTTP кодами (например 404)
 * 7. авторизация ОБЯЗАТЕЛЬНА для всех методов кроме GLOBAL и LOGIN
 * 8. соотвественно все методы GLOBAL - анонимны
 * 9. методы регистро НЕ зависимые
 */
class Apiv2_Handler extends Api implements \RouteHandler {

	// поддерживаемые методы (при создании новой группы заносятся вручную)
	public const ALLOW_CONTROLLERS = [
	];

	/**
	 * @inheritDoc
	 */
	public function getServedRoutes():array {

		return array_map(static fn(string $method) => str_replace("_", ".", $method), static::ALLOW_CONTROLLERS);
	}

	/**
	 * @inheritDoc
	 */
	public function getType():string {

		return "apiv2";
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

		// если нода только для чтения
		if (IS_READONLY) {
			throw new ParamException("Node is readonly");
		}

		// если нода только для чтения
		if (IS_READONLY) {
			throw new ParamException("Node is readonly");
		}

		$extra = [
			"namespace"              => __NAMESPACE__,
			"api_type"               => $this->getType(),
			"sharding_gateway_class" => ShardingGateway::class,
			"post_data"              => $post_data,
			"api_method"             => $route,
			"user_agent"             => getUa(),
		];

		// здесь описана последовательность проверок
		$router = new \BaseFrame\Router\Middleware([
			\BaseFrame\Router\Middleware\ValidateRequest::class,
			\BaseFrame\Router\Middleware\ModifyHandler::class,
			\BaseFrame\Router\Middleware\SetMethodVersion::class,
			\BaseFrame\Router\Middleware\InitializeController::class,
			\BaseFrame\Router\Middleware\Run::class,
			\BaseFrame\Router\Middleware\ValidateResponse::class,
		]);

		// выполняем все мидлвары по очереди
		$response = $router->handler($route, $post_data, $extra);

		// если запуск был не из консоли - закрываем соединения
		$this->_closeConnectionsIfRunFromNotCli();

		return $response;
	}
}