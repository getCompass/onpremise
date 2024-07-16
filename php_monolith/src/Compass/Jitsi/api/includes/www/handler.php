<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Handler\Api;
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
class Www_Handler extends Api implements \RouteHandler {

	// поддерживаемые методы (при создании новой группы заносятся вручную)
	public const ALLOW_CONTROLLERS = [
		"jitsi",
	];

	/**
	 * @inheritDoc
	 */
	public function getServedRoutes():array {

		return static::ALLOW_CONTROLLERS;
	}

	/**
	 * @inheritDoc
	 */
	public function getType():string {

		return "www";
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
			"namespace" => __NAMESPACE__,
			"api_type"  => "www",
		];

		// здесь описана последовательность проверок
		$router = new \BaseFrame\Router\Middleware([
			\BaseFrame\Router\Middleware\ValidateRequest::class,
			Middleware_WwwCookieGuestId::class,
			\BaseFrame\Router\Middleware\ModifyHandler::class,
			\BaseFrame\Router\Middleware\InitializeController::class,
			\BaseFrame\Router\Middleware\Run::class,
			\BaseFrame\Router\Middleware\ValidateResponse::class,
		]);

		// выполняем все мидлвары по порядку
		try {
			$response = $router->handler($route, $post_data, $extra);
		} catch (CaseException $e) {
			$response = self::handleCaseError($e);
		}

		// если запуск был не из консоли - закрываем соединения
		self::_closeConnectionsIfRunFromNotCli();

		// отдаем финальный ответ
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

		// возвращаем ответ
		return [
			"status"      => "error",
			"response"    => (object) $response_body,
			"server_time" => time(),
		];
	}

	// закрываем соединения, если запускали не из консоли
	protected function _closeConnectionsIfRunFromNotCli():void {

		// если запуск был не из консоли - закрываем соединения
		if (!isCLi()) {
			\sharding::end();
		}
	}
}