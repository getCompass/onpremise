<?php

namespace Compass\Conversation;

/**
 * класс обработки событий из очереди conversation
 * вызывается кроном
 */
class SystemEvent_Handler {

	// проверяет возможность выполнения задачи и при возможности выполняет ее
	public static function doStart(array $event):bool {

		if (!Type_SystemEvent_Main::validate($event)) {
			return false;
		}

		// получаем имя класса-обработчика
		$category_handler = self::_getEventCategoryHandler($event["event_type"]);

		if ($category_handler == "") {
			return false;
		}

		/** @noinspection PhpUndefinedMethodInspection */
		return $category_handler::work($event);
	}

	// возвращает класс-обработчик для категории событий
	protected static function _getEventCategoryHandler(string $method):string {

		// разбиваем категорию и метод
		$splitted = explode(".", $method, 2);
		if (count($splitted) != 2) {
			return "";
		}

		// формируем имя класса
		$category     = ucfirst(strtolower($splitted[0]));
		$worker_class = __NAMESPACE__ . "\\SystemEvent_$category";

		return class_exists($worker_class) ? $worker_class : "";
	}
}