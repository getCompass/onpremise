<?php

namespace BaseFrame\Handler;

use BaseFrame\Exception\Request\ControllerMethodNotFoundException;
use sharding;

/**
 * хендлер сокета
 */
abstract class Socket {

	// разрешенные контроллеры
	public const ALLOW_CONTROLLERS = [];

	/**
	 * Выполняем метод из контроллера
	 *
	 * @param array $post_data
	 *
	 * @return array
	 */
	abstract public static function doStart(array $post_data):array;

	// выбрасываем ошибку, если контроллер недоступен
	protected static function _throwIfControllerIsNotAllowed(string $method):void {

		$method = explode(".", strtolower($method));

		// если количество аргументов пришло неверное то выбрасываем что такого контроллера нет
		if (count($method) < 2 || count($method) > 3) {
			throw new ControllerMethodNotFoundException("CONTROLLER is not found.");
		}

		$controller = $method[0];

		// для поддержки 3 уровня
		if (count($method) === 3) {
			$controller .= "_" . $method[1];
		}

		// приводим все контроллеры из списка к нижнему регистру
		$allow_controllers = array_map("strtolower", static::ALLOW_CONTROLLERS);

		// проверяем доступные контролеры
		if (!in_array($controller, $allow_controllers)) {
			throw new ControllerMethodNotFoundException("CONTROLLER is not allowed. Check Handler allow controller.");
		}
	}

	// закрываем соединения, если запускали не из консоли
	protected static function _closeConnectionsIfRunFromNotCli():void {

		// если запуск был не из консоли - закрываем соединения
		if (!isCLi()) {
			sharding::end();
		}
	}
}