<?php

namespace Compass\Company;

/**
 * Класс для регистрации событий в системе
 * работает только через рэббит, пушит события в модуль событий
 */
class Gateway_Bus_SystemEvent {

	protected const _EVENT_SERVICE_METHOD = "event.dispatch";

	// пушит событие в очередь модуля событий
	public static function pushEvent(array $event_data):void {

		$ar_post = [
			"method" => self::_EVENT_SERVICE_METHOD,
			"event"  => $event_data,
		];

		// отправляем задачу в rabbitMq
		\Bus::rabbitSendToExchange(GO_EVENT_SERVICE_EVENT_QUEUE, $ar_post);
	}
}