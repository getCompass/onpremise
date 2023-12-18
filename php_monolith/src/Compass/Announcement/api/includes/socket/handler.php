<?php

namespace Compass\Announcement;

use BaseFrame\Handler\Api;

/**
 * ЗАДАЧА КЛАССА связывать между собой проекты и их взаимодействие
 * внутренне безопасное сервсиное общение между микросервисами
 */
class Socket_Handler extends Api implements \RouteHandler {

	public const ALLOW_CONTROLLERS = [
		"announcement",
	];

	/**
	 * @inheritDoc
	 */
	public function getServedRoutes():array {

		return array_map(static fn(string $controller) => "announcement" . "." . str_replace("_", ".", $controller), static::ALLOW_CONTROLLERS);
	}

	/**
	 * @inheritDoc
	 */
	public function getType():string {

		return "socket";
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

		$post_data["is_local"] = false;
		$method                = explode(".", strtolower($post_data["method"]));

		// если количество аргументов пришло неверное то выбрасываем что такого контроллера нет
		if (count($method) < 2 || count($method) > 3) {
			throw new \socketAccessException("CONTROLLER is not found.");
		}

		$controller  = $method[0];
		$method_name = $method[1];

		// для поддержки 3 уровня
		if (count($method) === 3) {

			$controller  .= "_" . $method[1];
			$method_name = $method[2];
		}

		// выбрасываем ошибку, если контроллер недоступен
		self::_throwIfControllerIsNotAllowed($controller);

		// выбрасываем ошибку, если не пришел метод
		self::_throwIfMethodIsNotSet($method_name);

		// устанавливаем параметры запроса
		$sender_module = $post_data["sender_module"];

		// получаем список доступных методов из конфига
		$module_config = self::_tryGetModuleConfig($sender_module);
		$allow_methods = self::_getAllowMethods($module_config);
		$json_params   = $post_data["json_params"];

		// выбрасываем ошибку, если нельзя дергать метод или подпись не сошлась
		self::_throwIfMethodIsNotAllowed($post_data["method"], $allow_methods);
		self::_throwIfMethodIsNotAgreed($post_data["signature"], $json_params, $module_config);

		$extra = [
			"namespace" => __NAMESPACE__,
			"api_type"  => "socket",
			"socket"    => [
				"allow_config" => getConfig("SOCKET_ALLOW_KEY"),
			],
		];

		// здесь описана последовательность проверок
		$router = new \BaseFrame\Router\Middleware([
			\BaseFrame\Router\Middleware\ValidateRequest::class,
			\BaseFrame\Router\Middleware\SocketAuthorization::class,
			\BaseFrame\Router\Middleware\ModifyHandler::class,
			\BaseFrame\Router\Middleware\InitializeController::class,
			\BaseFrame\Router\Middleware\Run::class,
			\BaseFrame\Router\Middleware\ValidateResponse::class,
		]);

		// выполняем все мидлвары по порядку
		$response = $router->handler($post_data["method"], $post_data, $extra);

		// если что-то некорректное пришло в ответе (например забыли вернуть return $this->ok)
		self::_throwIfStatusIsNotSet($response);

		// закрываем соедиенения, если запускали не из консоли
		$this->_closeConnectionsIfRunFromNotCli();

		return $response;
	}

	// выбрасываем ошибку, если контроллер недоступен
	protected static function _throwIfControllerIsNotAllowed(string $controller):void {

		// приводим все контроллеры из списка к нижнему регистру
		$allow_controllers = array_map("strtolower", self::ALLOW_CONTROLLERS);

		// проверяем доступные контролеры
		if (!in_array($controller, $allow_controllers)) {
			throw new \socketAccessException("CONTROLLER is not allowed. Check Handler allow controller.");
		}
	}

	// выбрасываем ошибку, если не задали метод
	protected static function _throwIfMethodIsNotSet(string $method):void {

		// проверяем что задан метод внутри контролера
		if (mb_strlen($method) < 1) {
			throw new \socketAccessException("CONTROLLER method name is EMPTY. Check method action name.");
		}
	}

	// возвращает конфиг ключей разрешенных для проекта
	protected static function _tryGetModuleConfig(string $sender_module):array {

		$allow_conf = getConfig("SOCKET_ALLOW_KEY");

		// проверяем есть ли конфиг для нашего модуля
		if (!isset($allow_conf[$sender_module])) {
			throw new \userAccessException("Sender module doesn't have access to current module");
		}

		return $allow_conf[$sender_module];
	}

	// получаем, что метод в списке доверенных
	protected static function _getAllowMethods(array $module_conf):array {

		// берем глобальный конфиг
		$allow_methods = $module_conf["allow_methods"];

		// приводим методы к нижнему регистру
		$allow_methods = array_change_key_case($allow_methods, CASE_LOWER);

		// приводим методы к нижнему регистру
		$allow_methods = array_map("strtolower", $allow_methods);

		return $allow_methods;
	}

	// выбрасываем ошибку, если нельзя дергать метод
	protected static function _throwIfMethodIsNotAllowed(string $api_method, array $allow_methods):void {

		// проверяем можно ли дергать метод
		if (!in_array(strtolower($api_method), $allow_methods)) {
			throw new \userAccessException("User not authorized for this actions.");
		}
	}

	// выбрасываем ошибку, если полпись не сошлась
	protected static function _throwIfMethodIsNotAgreed(string $signature, string $json_params, array $module_conf):void {

		$public_key = $module_conf["auth_key"];

		// сверяем подпись
		if (!Type_Socket_Auth_Handler::isSignatureAgreed($module_conf["auth_type"], $signature, $json_params, $public_key)) {
			throw new \userAccessException("User not authorized for this actions.");
		}
	}

	// возвращаем ответ метода
	protected static function _getResponse(string $controller, string $method_name, string $post_data, string $sender_module, int $user_id, int $company_id):array {

		// возвращаем работу метода
		$class = "Socket_{$controller}";

		return (new $class())->work($method_name, fromJson($post_data), $sender_module, $user_id, $company_id);
	}

	// выбрасываем ошибку, если вернулось что-то некорректное
	protected static function _throwIfStatusIsNotSet(array $output):void {

		// если что-то некорректное пришло в ответе (например забыли вернуть return $this->ok)
		if (!isset($output["status"])) {
			throw new \returnException("Final api response have not status.");
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
