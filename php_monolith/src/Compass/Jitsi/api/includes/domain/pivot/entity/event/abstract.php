<?php

namespace Compass\Jitsi;

use BaseFrame\Server\ServerProvider;

/**
 * Абстрактный класс описывающий любое событие, которое будет отправлено в пивот
 */
class Domain_Pivot_Entity_Event_Abstract {

	/**
	 * Должен переопределяться каждым событием и содержать строковый тип события
	 */
	protected const _EVENT_TYPE = null;

	/**
	 * Название модуля пивота
	 */
	private const _MODULE_NAME = "php_pivot";

	/**
	 * Отправляем событие в партнерскую программу
	 *
	 * @param array $event_data
	 *
	 * @throws \busException
	 */
	protected static function _sendToPivot(array $event_data):void {

		Gateway_Bus_Event::pushTask(static::_EVENT_TYPE, $event_data, self::_MODULE_NAME);
	}
}