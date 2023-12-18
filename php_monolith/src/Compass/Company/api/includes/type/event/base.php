<?php

namespace Compass\Company;

/**
 * Базовый класс любого события.
 */
class Type_Event_Base {

	/** @var int текущая версия события */
	const _CURRENT_VERSION = 1;

	/**
	 * Создает событие.
	 *
	 * @param string         $event_type
	 * @param Struct_Default $event_data
	 *
	 * @return Struct_Event_Base
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 */
	public static function create(string $event_type, Struct_Default $event_data):Struct_Event_Base {

		// копируем дефолтную структуру
		return Struct_Event_Base::build(
			generateUUID(),
			$event_type,
			timeUs(),
			self::_CURRENT_VERSION,
			$event_data
		);
	}
}
