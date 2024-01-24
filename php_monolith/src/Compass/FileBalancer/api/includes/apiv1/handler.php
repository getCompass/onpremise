<?php

namespace Compass\FileBalancer;

use BaseFrame\Handler\Api;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * примечания:
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
class ApiV1_Handler extends Api implements \RouteHandler {

	// поддерживаемые методы (при создании новой группы заносятся вручную)
	public const ALLOW_CONTROLLERS = [
		"files",
	];

	/**
	 * Возвращает обслуживаемые методы.
	 * @return string[]
	 */
	public function getServedRoutes():array {

		return array_map(static fn(string $method) => str_replace("_", ".", $method), static::ALLOW_CONTROLLERS);
	}

	/**
	 * Возвращает тип обработчика.
	 * @return string[]
	 */
	public function getType():string {

		return "apiv1";
	}

	/**
	 * ToString конвертация.
	 * @return string[]
	 */
	public function __toString():string {

		return static::class;
	}

	// единая точка входа в API
	// в качестве параметров принимает:
	//	@$api_method 	название метода вида test.code401
	//	@$post_data 	параметры post запроса которые будут использоваться внутри контролеров
	//	@user_id	для отладки и тестов. действуйет только в режиме cli
	// @long
	public function handle(string $route, array $post_data, int $user_id = 0):array {

		$extra = [
			"company_cache_class"           => Gateway_Bus_CompanyCache::class,
			"namespace"                     => __NAMESPACE__,
			"api_type"                      => $this->getType(),
			"not_auth_controller_list"      => [],
			"allowed_without_controller"    => true,
			"sharding_gateway_class"        => ShardingGateway::class,
			"action_not_allowed_error_code" => Permission::ACTION_NOT_ALLOWED_ERROR_CODE,
		];

		try {

			$authorization_class = match (CURRENT_SERVER) {
				CLOUD_SERVER => \CompassApp\Controller\Middleware\Session\Authorization::class,
				PIVOT_SERVER => Middleware_PivotAuthorization::class,
			};

			// если запускаем из консоли то без авторизации
			if ($user_id > 0 && isCLi()) {

				$extra["need_user_id"] = $user_id;
				$authorization_class   = Middleware_WithoutAuthorization::class;
			}

			// здесь описана последовательность проверок
			$middleware_list = match (CURRENT_SERVER) {
				CLOUD_SERVER => [
					\CompassApp\Controller\Middleware\CheckCompany::class,
					Middleware_InitializeConf::class,
					\BaseFrame\Router\Middleware\ValidateRequest::class,
					$authorization_class,
					\BaseFrame\Router\Middleware\ModifyHandler::class,
					\BaseFrame\Router\Middleware\InitializeController::class,
					\BaseFrame\Router\Middleware\SetMethodVersion::class,
					\CompassApp\Controller\Middleware\AddActivityToken::class,
					\CompassApp\Controller\Middleware\AddCustomAction::class,
					\BaseFrame\Router\Middleware\Run::class,
					\BaseFrame\Router\Middleware\ValidateResponse::class,
					\CompassApp\Controller\Middleware\Secure::class,
				],
				PIVOT_SERVER => [
					\BaseFrame\Router\Middleware\ValidateRequest::class,
					$authorization_class,
					\BaseFrame\Router\Middleware\ModifyHandler::class,
					\BaseFrame\Router\Middleware\SetMethodVersion::class,
					\BaseFrame\Router\Middleware\InitializeController::class,
					\CompassApp\Controller\Middleware\AddCustomAction::class,
					\BaseFrame\Router\Middleware\Run::class,
					\BaseFrame\Router\Middleware\ValidateResponse::class,
					\CompassApp\Controller\Middleware\Secure::class,
				]
			};

			$router = new \BaseFrame\Router\Middleware($middleware_list);

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
		self::_closeConnectionsIfRunFromNotCli();

		// перед ответом превращаем все map в key и отдаем финальный ответ
		return $response;
	}
}