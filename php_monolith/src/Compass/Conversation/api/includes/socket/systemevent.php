<?php

namespace Compass\Conversation;

/**
 * сокет методы для пересылки событий
 */
class Socket_SystemEvent extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы, регистр не имеет значение
	public const ALLOW_METHODS = [
		"processEventOnDpc",
	];

	// получает системное событие с другого дпс и обрабатывает его
	public function processEventOnDpc():array {

		$event = $this->post("?a", "event");

		if (SystemEvent_Handler::doStart($event)) {
			return $this->ok();
		} else {
			return $this->error(400);
		}
	}
}