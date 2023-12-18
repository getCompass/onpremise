<?php

namespace Compass\Company;

/**
 * базовый класс, который обрабатывает события из очереди событий
 * обработчики типов должны наследовать от нет
 */
class SystemEvent_Default {

	// список тип события -> метод обработчик
	protected const _ALLOWED_METHODS = [];

	// запускает метод
	public static function work(array $event):bool {

		$method = static::_getMethod($event);
		return $method != "" ? static::$method($event) : false;
	}

	// проверяет наличие метода-обработчика
	public static function _getMethod(array $event):string {

		// парсим тип сообщения и выбираем метод
		$splitted = explode(".", $event["event_type"], 2);
		$key      = strtolower($splitted[1]);

		return static::_ALLOWED_METHODS[$key] ?? "";
	}
}
