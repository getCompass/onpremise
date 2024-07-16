<?php

namespace Compass\Jitsi;

/**
 * Абстрактный класс описывающий любое событие, которое будет отправлено в jitsi-модуль
 */
class Domain_PhpJitsi_Entity_Event_Abstract {

	/**
	 * Должен переопределяться каждым событием и содержать строковый тип события
	 */
	protected const _EVENT_TYPE = null;

	/**
	 * Название модуля premise
	 */
	private const _MODULE_NAME = "php_jitsi";

	/**
	 * Отправляем событие в jitsi-модуль
	 *
	 * @param array $event_data
	 * @param int   $need_work
	 *
	 * @throws \busException
	 */
	protected static function _sendToJitsi(array $event_data, int $need_work = 0):void {

		Gateway_Bus_Event::pushTask(static::_EVENT_TYPE, $event_data, self::_MODULE_NAME, need_work: $need_work);
	}
}