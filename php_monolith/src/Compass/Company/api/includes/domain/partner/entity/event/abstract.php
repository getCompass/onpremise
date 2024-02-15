<?php

namespace Compass\Company;

use BaseFrame\Server\ServerProvider;

/**
 * Абстрактный класс описывающий любое событие, которое будет отправлено в ПП
 */
class Domain_Partner_Entity_Event_Abstract {

	/**
	 * Должен переопределяться каждым событием и содержать строковый тип события
	 */
	protected const _EVENT_TYPE = null;

	/**
	 * Название модуля ПП
	 */
	private const _MODULE_NAME = "php_partner";

	/**
	 * Отправляем событие в ПП
	 *
	 * @param array $event_data
	 *
	 * @throws \busException
	 */
	protected static function _sendToPartner(array $event_data):void {

		if (ServerProvider::isOnPremise()) {
			return;
		}

		Gateway_Bus_Event::pushTask(static::_EVENT_TYPE, $event_data, self::_MODULE_NAME);
	}
}