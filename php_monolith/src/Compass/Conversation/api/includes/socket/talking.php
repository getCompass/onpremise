<?php

namespace Compass\Conversation;

/**
 * контроллер для сокет методов класса talking
 */
class Socket_Talking extends \BaseFrame\Controller\Socket {

	// Поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"getUserConnections",
		"closeUserConnections",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	// метод получения списка соединений пользователя
	public function getUserConnections():array {

		// обращаемся к go_talking_handler
		$connection_list = Gateway_Bus_Sender::getOnlineConnectionsByUserId($this->user_id);

		return $this->ok([
			"connection_list" => (array) $connection_list,
		]);
	}

	// метод разрыва соединения пользователя
	public function closeUserConnections():array {

		// обращаемся к go_talking_handler
		Gateway_Bus_Sender::closeConnectionsByUserId($this->user_id);

		return $this->ok();
	}
}