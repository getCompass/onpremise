<?php

namespace Compass\Thread;

/**
 * Системный класс
 */
class Socket_Event extends \BaseFrame\Controller\Socket {

	public const ALLOW_METHODS = [
		"processEvent",
		"processEventList",
	];

	/**
	 * Обработать событие
	 *
	 * @throws \ReflectionException
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function processEvent():array {

		$event = $this->post(\Formatter::TYPE_ARRAY, "event");

		$handler = Type_Event_Handler::instance();
		$handler->handle($event);

		return $this->ok();
	}

	/**
	 * Обработать событие
	 *
	 * @return array
	 *
	 * @throws \paramException
	 * @throws \parseException
	 */
	public function processEventList():array {

		$event_list = $this->post(\Formatter::TYPE_ARRAY, "event_list");

		$handler       = Type_Event_Handler::instance();
		$delivery_info = $handler->handleList($event_list);

		return $this->ok([
			"event_process_result_list" => (object) $delivery_info,
		]);
	}
}
