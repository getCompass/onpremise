<?php

namespace Compass\Userbot;

use BaseFrame\Exception\Domain\ReturnFatalException;

/**
 * ЗАДАЧА КЛАССА связывать между собой проекты и их взаимодействие
 * внутренне безопасное сервсиное общение между микросервисами
 */
class Socket_Handler implements \RouteHandler{

	public const ALLOW_CONTROLLERS = [
		"system",
		"userbot",
	];

	/**
	 * @inheritDoc
	 */
	public function getServedRoutes():array {

		return array_map(static fn(string $controller) => CURRENT_MODULE . "." . str_replace("_", ".", $controller), static::ALLOW_CONTROLLERS);
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

	// в качестве параметров принимает:
	//	@$api_method 	название метода вида test.code401
	//	@$post_data 	параметры post запроса которые будут использоваться внутри контролеров
	//	@user_id	для отладки и тестов. действуйет только в режиме cli
	// @long
	public function handle(string $route, mixed $post_data):array {

		$extra = [
			"namespace" => __NAMESPACE__,
			"api_type"  => $this->getType(),
			"socket"    => [
				"allow_config" => getConfig("SOCKET_ALLOW_KEY"),
			],
		];

		// здесь описана последовательность проверок
		$router = new \BaseFrame\Router\Middleware([
			\BaseFrame\Router\Middleware\ValidateRequest::class,
			\BaseFrame\Router\Middleware\SocketAuthorization::class,
			\BaseFrame\Router\Middleware\ModifyHandler::class,
			\BaseFrame\Router\Middleware\SetMethodVersion::class,
			\BaseFrame\Router\Middleware\InitializeController::class,
			\BaseFrame\Router\Middleware\Run::class,
			\BaseFrame\Router\Middleware\ValidateResponse::class,
		]);

		$response = $router->handler($route, $post_data, $extra);

		// если какой-то левак пришел в ответе (например забыли вернуть return $this->ok)
		self::_throwIfStatusIsNotSet($response);

		// закрываем соедиенения, если запускали не из консоли
		self::_closeConnectionsIfRunFromNotCli();

		return $response;
	}

	// выбрасываем ошибку, если вернулся какой-то левак
	protected static function _throwIfStatusIsNotSet(array $output):void {

		// если какой-то левак пришел в ответе (например забыли вернуть return $this->ok)
		if (!isset($output["status"])) {
			throw new ReturnFatalException("Final api response have not status.");
		}
	}

	// закрываем соединения, если запускали не из консоли
	protected static function _closeConnectionsIfRunFromNotCli():void {

		// если запуск был не из консоли - закрываем соединения
		if (!isCLi()) {
			\sharding::end();
		}
	}
}
