<?php

namespace Compass\Thread;

use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Request\PaymentRequiredException;
use BaseFrame\Handler\Api;
use BaseFrame\Server\ServerProvider;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * Примечания:
 * 1. Все запросы к API методом POST
 * 2. Все ответы в формате JSON
 * 3. У всех ответов есть поле status (ok||error) && response
 * 4. Кодировка UTF-8
 * 5. Все ошибки задокументированы в апи документаторе для каждого метода
 * 6. Ошибки и исклчюения отрабатываются сервер HTTP кодами (например 404)
 * 7. Авторизация ОБЯЗАТЕЛЬНА для всех методов кроме GLOBAL и LOGIN
 * 8. Соотвественно все методы GLOBAL - анонимны
 * 9. Методы регистро НЕ зависимые
 */
class Apiv1_Handler extends Api implements \RouteHandler {

	// поддерживаемые методы (при создании новой группы заносятся в ручную)
	public const ALLOW_CONTROLLERS = [
		"threads",
	];

	// методы, которые запрещены на on-premise
	public const NOT_ALLOWED_METHODS_FOR_ON_PREMISE = [];

	/**
	 * Возвращает обслуживаемые методы.
	 * @return string[]
	 */
	public function getServedRoutes():array {

		return static::ALLOW_CONTROLLERS;
	}

	/**
	 * Возвращает тип обработчика.
	 * @return string
	 */
	public function getType():string {

		return "apiv1";
	}

	/**
	 * ToString конвертация.
	 * @return string
	 */
	public function __toString():string {

		return static::class;
	}

	/**
	 * Точка входа в обработчик.
	 * Возвращает результат исполнения метода.
	 *
	 * @param string $route
	 * @param array  $post
	 * @param int    $user_id
	 *
	 * @return array
	 * @throws \paramException
	 * @long
	 */
	public function handle(string $route, array $post_data, int $user_id = 0):array {

		$extra               = [
			"namespace"                     => __NAMESPACE__,
			"api_type"                      => $this->getType(),
			"company_cache_class"           => Gateway_Bus_CompanyCache::class,
			"sharding_gateway_class"        => ShardingGateway::class,
			"action_not_allowed_error_code" => Permission::ACTION_NOT_ALLOWED_ERROR_CODE,
			"not_allowed_method_list"       => [],
		];
		$authorization_class = \CompassApp\Controller\Middleware\Session\Authorization::class;

		if (ServerProvider::isOnPremise()) {
			$extra["not_allowed_method_list"] = array_merge($extra["not_allowed_method_list"], self::NOT_ALLOWED_METHODS_FOR_ON_PREMISE);
		}

		// если запускаем из консоли то без авторизации
		if ($user_id > 0 && isCLi()) {

			$extra["need_user_id"] = $user_id;
			$authorization_class   = Middleware_WithoutAuthorization::class;
		}

		$router = new \BaseFrame\Router\Middleware([
			\CompassApp\Controller\Middleware\CheckCompany::class,
			Middleware_InitializeConf::class,
			$authorization_class,
			\BaseFrame\Router\Middleware\ValidateRequest::class,
			\BaseFrame\Router\Middleware\ModifyHandler::class,
			\BaseFrame\Router\Middleware\InitializeController::class,
			Middleware_CheckHasRestrictedAccess::class,
			\BaseFrame\Router\Middleware\SetMethodVersion::class,
			\CompassApp\Controller\Middleware\AddActivityToken::class,
			\CompassApp\Controller\Middleware\Session\PremiumStatus::class,
			\CompassApp\Controller\Middleware\AddCustomAction::class,
			\BaseFrame\Router\Middleware\CheckUserRoleMethodAccess::class,
			\BaseFrame\Router\Middleware\Run::class,
			\BaseFrame\Router\Middleware\UpdateHeader::class,
			\BaseFrame\Router\Middleware\ValidateResponse::class,
			\CompassApp\Controller\Middleware\Secure::class,
			\BaseFrame\Router\Middleware\ApplicationUserTimeSpent::class,
		]);

		$extra["need_user_id"] = $user_id;
		try {

			$response = $router->handler($route, $post_data, $extra);
		} catch (cs_AnswerCommand $e) {

			// если вдруг поймали команду
			return [
				"command" => Type_Api_Command::work($e->getCommandName(), $e->getCommandExtra()),
			];
		} catch (cs_PlatformNotFound) {
			throw new ParamException("Platform not found");
		} catch (\cs_DecryptHasFailed) {
			throw new ParamException("decrypt key was failed");
		} catch (CaseException|PaymentRequiredException $e) {
			return $this->_handleBusinessError($e);
		}

		// если запуск был не из консоли - закрываем соединения
		if (!isCLi()) {
			\sharding::end();
		}

		// отдаем финальный ответ
		return $response;
	}
}