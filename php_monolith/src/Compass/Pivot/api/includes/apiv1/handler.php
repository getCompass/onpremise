<?php

/**
 * api Handler - version 2.0
 * задачи класса - максимально быстро с минимальными запросами отдавать информацию для чтения.
 */

namespace Compass\Pivot;

use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Handler\Api;
use BaseFrame\Server\ServerProvider;
use JetBrains\PhpStorm\ArrayShape;

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
class Apiv1_Handler extends Api implements \RouteHandler {

	// поддерживаемые методы (при создании новой группы заносятся вручную)
	public const ALLOW_CONTROLLERS = [
		"pivot_auth",
		"pivot_profile",
		"pivot_calls",
		"pivot_security",
		"pivot_mbti",
		"global",
		"company",
		"notifications",
		"talking",
		"phone",
		"announcement",
	];

	// поддерживаемые методы которые доступны без авторизации (при создании новой группы заносятся вручную)
	public const ALLOWED_NOT_AUTHORIZED = [
		"pivot_auth",
		"global",
	];

	// контроллеры и методы, которые разрешены при пустом профиле
	public const ALLOWED_METHODS_FOR_EMPTY_PROFILE = [
		"global.doStart",
		"global.getStartData",
		"global.getPublicDocuments",
		"global.getCountryFlagList",
		"pivot.auth.doStart",
		"pivot.auth.tryConfirm",
		"pivot.auth.doResend",
		"pivot.auth.doLogout",
		"pivot.profile.set",
	];

	// методы, которые доступны только для рута на on-premise
	public const ALLOWED_METHODS_ONLY_FOR_ROOT_ON_PREMISE = [
		"company.add",
		"company.delete",
	];

	// методы, которые запрещены на on-premise
	public const NOT_ALLOWED_METHODS_FOR_ON_PREMISE = [];

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

		return "apiv1";
	}

	/**
	 * @inheritDoc
	 */
	public function __toString():string {

		return static::class;
	}

	/**
	 * @inheritDoc
	 * @long
	 */
	public function handle(string $route, array $post_data, int $user_id = 0):array {

		// фиксируем идентификатор запроса
		$request_id = (new \BaseFrame\Http\Header\RequestId())->getValue();

		// инициализируем технический мониторинг;
		// вообще это делать нужно не здесь, а где-то выше по стеку,
		// но в силу того, что подключения к сервисам
		// закрываются тут, то и объявляем все это дело тут
		\BaseFrame\Monitor\Core::init(Gateway_Bus_CollectorAgent::init(), ...(getConfig("MONITOR")["FLAG"]));
		\BaseFrame\Monitor\Core::getTraceAggregator()->init($request_id, "php_" . CURRENT_MODULE, true);
		\BaseFrame\Monitor\Core::getMetricAggregator()->setDefaultLabel("module", "php_" . CURRENT_MODULE);
		\BaseFrame\Monitor\Core::getLogAggregator()
			->setDefaultLabel("module", "php_" . CURRENT_MODULE)
			->setDefaultLabel("request_id", $request_id)
			->setLogLevel(\BaseFrame\Monitor\LogAggregator::LOG_LEVEL_INFO);

		// запускаем сбор метрик контроллера
		$metric = \BaseFrame\Monitor\Core::metric("api_entrypoint_execution_time_ms");

		$extra = [
			"namespace"                     => __NAMESPACE__,
			"api_type"                      => "apiv1",
			"not_auth_controller_list"      => self::ALLOWED_NOT_AUTHORIZED,
			"allowed_without_controller"    => true,
			"allowed_with_empty_profile"    => self::ALLOWED_METHODS_FOR_EMPTY_PROFILE,
			"post_data"                     => $post_data,
			"api_method"                    => $route,
			"user_agent"                    => getUa(),
			"not_allowed_method_list"       => [],
			"allowed_methods_only_for_root" => [],
		];

		if (ServerProvider::isOnPremise()) {

			$extra["not_allowed_method_list"]       = array_merge($extra["not_allowed_method_list"], self::NOT_ALLOWED_METHODS_FOR_ON_PREMISE);
			$extra["allowed_methods_only_for_root"] = array_merge($extra["allowed_methods_only_for_root"], self::ALLOWED_METHODS_ONLY_FOR_ROOT_ON_PREMISE);
		}

		try {

			$authorization_class = Middleware_PivotAuthorization::class;

			// если запускаем из консоли то без авторизации
			if ($user_id > 0 && isCLi()) {

				$extra["need_user_id"] = $user_id;
				$authorization_class   = Middleware_WithoutAuthorization::class;
			}

			$router = new \BaseFrame\Router\Middleware([
				\BaseFrame\Router\Middleware\ObserveExceptions::class,
				\BaseFrame\Router\Middleware\ValidateRequest::class,
				\BaseFrame\Router\Middleware\SetExceptionHandler::class,
				Middleware_AuthCookieToHeader::class,
				$authorization_class,
				\BaseFrame\Router\Middleware\ModifyHandler::class,
				\BaseFrame\Router\Middleware\InitializeController::class,
				\BaseFrame\Router\Middleware\SetMethodVersion::class,
				\BaseFrame\Router\Middleware\CheckAllowedMethod::class,
				Middleware_OnPremiseCheckMethod::class,
				Middleware_AddCustomAction::class,
				\BaseFrame\Router\Middleware\Run::class,
				\BaseFrame\Router\Middleware\ValidateResponse::class,
				\BaseFrame\Router\Middleware\AttachAuthData::class,
				\BaseFrame\Router\Middleware\ApplicationUserTimeSpent::class,
			]);

			// выполняем все мидлвары по порядку
			$response = $router->handler($route, $post_data, $extra);
		} catch (cs_AnswerCommand $e) {

			// если вдруг поймали команду
			$response  = ["command" => Type_Api_Command::work($e->getCommandName(), $e->getCommandExtra())];
			$auth_data = \BaseFrame\Http\Authorization\Data::inst();

			// если данные авторизации не менялись, то ничего не делаем
			if ($auth_data->hasChanges()) {
				$response["actions"][] = ["type" => "authorization", "data" => $auth_data->get()];
			}

			return $response;
		} catch (cs_PlatformNotFound) {
			throw new ParamException("Platform not found");
		} catch (\cs_DecryptHasFailed) {
			throw new ParamException("wrong key format");
		} catch (CaseException $e) {
			$response = self::handleCaseError($e);
		} catch (ControllerMethodNotFoundException){
			throw new \apiAccessException();
		}

		// перед ответом превращаем все map в key
		$response = Type_Pack_Main::replaceMapWithKeys($response);

		// отсылаем данные в коллектор
		$metric->since()->seal();
		\BaseFrame\Monitor\Core::flush();

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