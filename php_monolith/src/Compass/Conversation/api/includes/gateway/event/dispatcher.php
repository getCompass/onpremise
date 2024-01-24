<?php

declare(strict_types = 1);

namespace Compass\Conversation;

use BaseFrame\Exception\Domain\ParseFatalException;

/**
 * Класс регистрации событий.
 * Если какое-то событие произошло в системе, то его нужно зарегистрировать через этот метод.
 */
class Gateway_Event_Dispatcher {

	/**
	 * Пушит событие в систему.
	 *
	 * @param Struct_Event_Base $event
	 *
	 * @throws ParseFatalException
	 */
	public static function dispatch(Struct_Event_Base $event):void {

		$event = [
			"method" => "event.dispatch",
			"event"  => $event,
		];

		Gateway_Bus_Rabbit::sendMessage(GO_EVENT_GLOBAL_EVENT_QUEUE, (array) $event);
	}

	/**
	 * Пушит событие в систему.
	 *
	 */
	public static function dispatchService(Struct_Event_Base $event):void {

		$event = [
			"method" => "event.dispatch",
			"event"  => $event,
		];

		Gateway_Bus_Rabbit::sendMessageToExchange(GO_EVENT_SERVICE_EVENT_EXCHANGE, (array) $event);
	}
}
