<?php

namespace Compass\Pivot;

use BaseFrame\Server\ServerProvider;

/**
 * Абстрактный класс описывающий любое событие, которое будет отправлено в CRM
 */
class Domain_Crm_Entity_Event_Abstract {

	/**
	 * Должен переопределяться каждым событием и содержать строковый тип события
	 */
	protected const _EVENT_TYPE = null;

	/**
	 * Название модуля CRM
	 */
	private const _MODULE_NAME = "php_crm";

	/**
	 * Отправляем событие в CRM
	 *
	 * @param array $event_data
	 *
	 * @throws \busException
	 */
	protected static function _sendToCrm(array $event_data):void {

		if (ServerProvider::isOnPremise() || ServerProvider::isMaster()) {
			return;
		}

		Gateway_Bus_Event::pushTask(static::_EVENT_TYPE, $event_data, self::_MODULE_NAME);
	}
}