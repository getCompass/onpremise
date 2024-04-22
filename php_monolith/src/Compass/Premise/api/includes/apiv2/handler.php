<?php

namespace Compass\Premise;

use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Handler\Api;
use JetBrains\PhpStorm\ArrayShape;

/**
 * Обработчик запрос для клиентского Api второй версии.
 */
class Apiv2_Handler extends Api implements \RouteHandler {

	// поддерживаемые методы (при создании новой группы заносятся вручную)
	public const ALLOW_CONTROLLERS = [
		"premise",
		"premisespace",
		"premiseuser",
		"premiselicense",
		"premiseuser.premisepermissions",
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

		$extra = [
			"namespace"                  => __NAMESPACE__,
			"api_type"                   => "apiv2",
			"not_auth_controller_list"   => self::ALLOWED_NOT_AUTHORIZED,
			"allowed_without_controller" => true,
			"post_data"                  => $post_data,
			"api_method"                 => $route,
			"user_agent"                 => getUa(),
		];

		$authorization_class = Middleware_PivotAuthorization::class;

		// если запускаем из консоли то без авторизации
		if ($user_id > 0 && isCLi()) {

			$extra["need_user_id"] = $user_id;
			$authorization_class   = Middleware_WithoutAuthorization::class;
		}
		try {

			$router = new \BaseFrame\Router\Middleware([
				\BaseFrame\Router\Middleware\ObserveExceptions::class,
				\BaseFrame\Router\Middleware\ValidateRequest::class,
				\BaseFrame\Router\Middleware\SetExceptionHandler::class,
				$authorization_class,
				\BaseFrame\Router\Middleware\ModifyHandler::class,
				\BaseFrame\Router\Middleware\InitializeController::class,
				\BaseFrame\Router\Middleware\SetMethodVersion::class,
				Middleware_AddCustomAction::class,
				\BaseFrame\Router\Middleware\Run::class,
				\BaseFrame\Router\Middleware\ValidateResponse::class,
			]);

			// выполняем все мидлвары по порядку
			$response = $router->handler($route, $post_data, $extra);
		} catch (cs_PlatformNotFound) {
			throw new ParamException("Platform not found");
		} catch (\cs_DecryptHasFailed) {
			throw new ParamException("wrong key format");
		} catch (CaseException $e) {
			$response = self::handleCaseError($e);
		} catch (cs_AnswerCommand $e) {

			// если вдруг поймали команду
			return [
				"command" => Type_Api_Command::work($e->getCommandName(), $e->getCommandExtra()),
			];
		}

		// если запуск был не из консоли - закрываем соединения
		$this->_closeConnectionsIfRunFromNotCli();

		// перед ответом превращаем все map в key и отдаем финальный ответ
		return $response;
	}

	/**
	 * Обработка ошибки при совершении запроса
	 *
	 * @param CaseException $exception
	 *
	 * @return array
	 */
	#[ArrayShape(["status" => "string", "response" => "object", "server_time" => "int"])]
	public static function handleCaseError(CaseException $exception):array {

		$error_code = $exception->getErrorCode();

		// устанавливаем http code ошибки
		http_response_code($exception->getHttpCode());

		// формируем ответ
		$response_body = array_merge([
			"error_code" => $error_code,
		], $exception->getExtra());

		// фвозвращаем ответ
		return [
			"status"      => "error",
			"response"    => (object) $response_body,
			"server_time" => time(),
		];
	}

	// ---------------------------------------------------
	// PROTECTED UTILS METHODS
	// ---------------------------------------------------

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