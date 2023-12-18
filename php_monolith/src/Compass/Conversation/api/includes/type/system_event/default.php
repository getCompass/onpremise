<?php

namespace Compass\Conversation;

/**
 * класс, описывающий общий тип событий
 */
class Type_SystemEvent_Default {

	protected const _EVENT_STRUCTURE = [];

	// валидирует тип источника для события
	public static function validateSourceType(string $source_type):string {

		return $source_type;
	}

	// валидирует идентификтор для события
	public static function validateSourceId(string $source_id):string {

		return $source_id;
	}

	// возвращает структуру данных события определенного типа
	public static function getStructure(string $event_type):array|false {

		return isset(static::_EVENT_STRUCTURE[$event_type])
			? static::_EVENT_STRUCTURE[$event_type]
			: false;
	}
}
