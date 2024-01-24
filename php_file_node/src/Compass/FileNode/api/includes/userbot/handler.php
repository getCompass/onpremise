<?php

namespace Compass\FileNode;

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
class Userbot_Handler extends Api implements \RouteHandler {

	// поддерживаемые методы (при создании новой группы заносятся вручную)
	public const ALLOW_CONTROLLERS = [
		"files",
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

		return "userbot";
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
			"namespace"                  => __NAMESPACE__,
			"api_type"                   => $this->getType(),
			"not_auth_controller_list"   => [],
			"allowed_without_controller" => true,
			"sharding_gateway_class"     => ShardingGateway::class,
			"need_user_id"               => $user_id,
			"action"                     => Type_Api_Action::class,
		];

		try {

			$router = new \BaseFrame\Router\Middleware([
				\BaseFrame\Router\Middleware\ValidateRequest::class,
				Middleware_WithoutAuthorization::class,
				\BaseFrame\Router\Middleware\ModifyHandler::class,
				\BaseFrame\Router\Middleware\InitializeController::class,
				\BaseFrame\Router\Middleware\SetMethodVersion::class,
				\BaseFrame\Router\Middleware\Run::class,
				\BaseFrame\Router\Middleware\ValidateResponse::class,
				\BaseFrame\Router\Middleware\ValidateRequest::class,
			]);

			// выполняем все мидлвары по порядку
			$response = $router->handler($route, $post_data, $extra);
		} catch (cs_AnswerCommand $e) {

			// если вдруг поймали команду
			return [
				"command" => Type_Api_Command::work($e->getCommandName(), $e->getCommandExtra()),
			];
		} catch (cs_PlatformNotFound) {
			throw new \paramException("Platform not found");
		}

		// если запуск был не из консоли - закрываем соединения
		$this->_closeConnectionsIfRunFromNotCli();

		// перед ответом превращаем все map в key и отдаем финальный ответ
		return $response;
	}
}
