<?php declare(strict_types=1);

namespace Application\Entrypoint;

use BaseFrame\Exception\Request\ControllerMethodNotFoundException;

/**
 *
 */
class Resolver {

	/** @var Resolver|null экземпляр синглтона */
	protected static ?Resolver $_instance = null;

	/** @var \RouteHandler[][] список маршрутов для apiv1 */
	protected array $_handler_route_list = [];

	/**
	 * Скрываем конструктор, работаем через синглтон.
	 */
	protected function __construct() {

	}

	/**
	 * Метод инстанцирования экземпляра.
	 */
	public static function instance():static {

		if (is_null(static::$_instance)) {

			static::$_instance = new static();
		}

		return static::$_instance;
	}

	/**
	 * Создает привязку класс -> путь запроса.
	 */
	public function registerRoutesHandler(string $type, \RouteHandler $handler, array $route_list):void {

		$type = mb_strtolower($type);

		foreach ($route_list as $route) {

			// проверяем, что такой путь не занят, во благо избежания внезапных конфликтов
			if (isset($this->_handler_route_list[$type][$route])) {
				throw new \Exception("duplicate route {$route} handler passed — already served by {$this->_handler_route_list[$type][$route]}");
			}

			// регистрируем путь
			$this->_handler_route_list[$type][$route] = $handler;
		}
	}

	/**
	 * Выполняет получение обработчика для указанного пути.
	 */
	public function resolveHandlerByRoute(string $type, string $route):\RouteHandler {

		$type = mb_strtolower($type);

		if (!isset($this->_handler_route_list[$type][$route])) {
			throw new ControllerMethodNotFoundException("route {$route} has no handler");
		}

		return $this->_handler_route_list[$type][$route];
	}
}
