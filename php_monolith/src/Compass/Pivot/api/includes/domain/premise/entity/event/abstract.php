<?php

namespace Compass\Pivot;

/**
 * Абстрактный класс описывающий любое событие, которое будет отправлено в premise-модуль
 */
class Domain_Premise_Entity_Event_Abstract {

	/**
	 * Должен переопределяться каждым событием и содержать строковый тип события
	 */
	protected const _EVENT_TYPE = null;

	/**
	 * Название модуля premise
	 */
	private const _MODULE_NAME = "php_premise";

	/**
	 * Отправляем событие в premise-модуль
	 *
	 * @param array $event_data
	 *
	 * @throws \busException
	 */
	protected static function _sendToPremise(array $event_data):void {

		Gateway_Bus_Event::pushTask(static::_EVENT_TYPE, $event_data, self::_MODULE_NAME);
	}
}