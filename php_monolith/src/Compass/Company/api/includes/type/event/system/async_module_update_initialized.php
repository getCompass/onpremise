<?php

namespace Compass\Company;

/**
 * Событие — запущено асинхронное обновление модуля с помощью скрипта.
 *
 * @event_category system
 * @event_name     async_module_update_initialized
 */
class Type_Event_System_AsyncModuleUpdateInitialized {

	/** @var string тип события */
	public const EVENT_TYPE = "system.async_module_update_initialized";

	/**
	 * Создает события для вещания.
	 * Эта функция вызывается, когда событие пушится в систему.
	 *
	 * @throws \parseException
	 */
	public static function create(string $module_name, string $script_name, array $data):Struct_Event_Base {

		return Type_Event_Base::create(self::EVENT_TYPE, new Struct_Event_System_AsyncModuleUpdateInitialized($module_name, $script_name, $data));
	}

	/**
	 * Парсим событие и достаем из него данные.
	 * Эта функция вызывается, когда модуль получил событие по шине данных.
	 *
	 * @throws \parseException
	 */
	public static function parse(array $event):Struct_Event_System_AsyncModuleUpdateInitialized {

		return new Struct_Event_System_AsyncModuleUpdateInitialized(...$event["event_data"]);
	}
}
