<?php

namespace Compass\Conversation;

use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ParamException;
use BaseFrame\Exception\Request\PaymentRequiredException;
use BaseFrame\Handler\Api;
use BaseFrame\Server\ServerProvider;
use CompassApp\Domain\Member\Entity\Permission;

/**
 * api Handler - version 2.0
 * задачи класса - максимально быстро с минимальными запросами отдавать информацию для чтения.
 */

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

	// поддерживаемые методы (при создании новой группы заносятся в ручную)
	public const ALLOW_CONTROLLERS = [
		"conversations",
		"groups",
		"talking",
		"invites",
		"previews",
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
	public function handle(string $route, array $post, int $user_id = 0):array {

		$extra               = [
			"company_cache_class"           => Gateway_Bus_CompanyCache::class,
			"namespace"                     => __NAMESPACE__,
			"api_type"                      => $this->getType(),
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

		// здесь описана последовательность проверок
		$router = new \BaseFrame\Router\Middleware([
			\CompassApp\Controller\Middleware\CheckCompany::class,
			Middleware_InitializeConf::class,
			\BaseFrame\Router\Middleware\ValidateRequest::class,
			$authorization_class,
			\BaseFrame\Router\Middleware\ModifyHandler::class,
			\BaseFrame\Router\Middleware\InitializeController::class,
			Middleware_CheckHasRestrictedAccess::class,
			\BaseFrame\Router\Middleware\SetMethodVersion::class,
			\CompassApp\Controller\Middleware\AddActivityToken::class,
			\CompassApp\Controller\Middleware\Session\PremiumStatus::class,
			\CompassApp\Controller\Middleware\AddCustomAction::class,
			\BaseFrame\Router\Middleware\CheckUserRoleMethodAccess::class,
			\BaseFrame\Router\Middleware\CheckAllowedMethod::class,
			\BaseFrame\Router\Middleware\Run::class,
			\BaseFrame\Router\Middleware\UpdateHeader::class,
			\BaseFrame\Router\Middleware\ValidateResponse::class,
			\CompassApp\Controller\Middleware\Secure::class,
			\BaseFrame\Router\Middleware\ApplicationUserTimeSpent::class,
		]);

		try {

			// выполняем все мидлвары по порядку
			$response = $router->handler($route, $post, $extra);
		} catch (cs_AnswerCommand $e) {

			// если вдруг поймали команду
			return [
				"command" => Type_Api_Command::work($e->getCommandName(), $e->getCommandExtra()),
			];
		} catch (cs_PlatformNotFound) {
			throw new ParamException("Platform not found");
		} catch (\cs_DecryptHasFailed $e) {

			Type_System_Admin::log("decrypt failed ", "ip: " . getIp() . "message: " . $e->getMessage());
			throw new ParamException("decrypt key was failed");
		} catch (CaseException|PaymentRequiredException $e) {
			$response = $this->_handleBusinessError($e);
		}

		// если запуск был не из консоли - закрываем соединения
		$this->_closeConnectionsIfRunFromNotCli();

		// отдаем финальный ответ
		return $response;
	}
}