<?php

namespace BaseFrame\Handler;

use BaseFrame\Exception\Request\CaseException;
use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use BaseFrame\Exception\Request\PaymentRequiredException;
use JetBrains\PhpStorm\ArrayShape;
use sharding;

/**
 * хендлер апи
 */
abstract class Api {

	// разрешенные контроллеры
	public const ALLOW_CONTROLLERS = [];

	// поддерживаемые методы которые доступны без авторизации (при создании новой группы заносятся вручную)
	public const ALLOWED_NOT_AUTHORIZED = [];

	/**
	 * Выполняем метод из контроллера
	 *
	 * @param string $route
	 * @param array  $post_data
	 * @param int    $user_id
	 *
	 * @return array
	 */
	abstract public function handle(string $route, array $post_data, int $user_id):array;


	/**
	 * выбрасываем ошибку, если контроллер недоступен
	 *
	 * @param string $controller
	 *
	 * @return void
	 * @throws ControllerMethodNotFoundException
	 */
	protected static function _throwIfControllerIsNotAllowed(string $controller):void {

		// приводим все контроллеры из списка к нижнему регистру
		$allow_controllers = array_map("strtolower", static::ALLOW_CONTROLLERS);

		// проверяем доступные контролеры
		if (!in_array($controller, $allow_controllers)) {
			throw new ControllerMethodNotFoundException("CONTROLLER is not allowed. Check Handler allow controller.");
		}
	}

	/**
	 * Обработка ошибки бизнес-логики
	 *
	 * @param CaseException|PaymentRequiredException $exception
	 *
	 * @return array
	 */
	#[ArrayShape(["status" => "string", "response" => "object", "server_time" => "int"])]
	protected function _handleBusinessError(CaseException|PaymentRequiredException $exception):array {

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

	// закрываем соединения, если запускали не из консоли
	protected function _closeConnectionsIfRunFromNotCli():void {

		// если запуск был не из консоли - закрываем соединения
		if (!isCLi()) {
			sharding::end();
		}
	}
}