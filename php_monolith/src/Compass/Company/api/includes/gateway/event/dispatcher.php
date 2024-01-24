<?php

declare(strict_types = 1);

namespace Compass\Company;

/**
 * Класс регистрации событий.
 * Если какое-то событие произошло в системе, то его нужно зарегистрировать через этот метод.
 */
class Gateway_Event_Dispatcher {

	/** @var string шина для собственных событий */
	const _SELF_EVENT_BUS = CURRENT_MODULE . "_event";

	/**
	 * Пушит событие в систему.
	 */
	public static function dispatch(Struct_Event_Base $event, bool $is_global = false):void {

		if ($is_global) {

			$event = [
				"method" => "event.dispatch",
				"event"  => $event,
			];
			Gateway_Bus_Rabbit::sendMessage(GO_EVENT_GLOBAL_EVENT_QUEUE, (array) $event);
		} else {
			Gateway_Bus_Rabbit::sendMessage(self::_SELF_EVENT_BUS, (array) $event);
		}
	}

	/**
	 * Пушит событие в систему.
	 */
	public static function dispatchService(Struct_Event_Base $event):void {

		$event = [
			"method" => "event.dispatch",
			"event"  => $event,
		];
		Gateway_Bus_Rabbit::sendMessageToExchange(GO_EVENT_SERVICE_EVENT_EXCHANGE, (array) $event);
	}
}