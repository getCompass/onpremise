<?php

namespace Compass\FileBalancer;

/**
 * контроллер для сокет методов antispam
 */
class Socket_Antispam extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы, регистр не имеет значение
	public const ALLOW_METHODS = [
		"clearAll",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * Метод для очистки блокировок, если передан user_id чистим только по пользователю
	 */
	public function clearAll():array {

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id", 0);

		// проверяем, что передан корректный ID
		if ($user_id < 0) {
			return $this->error(400, "User id is incorrect");
		}

		Domain_System_Scenario_Socket::clearAntispam($user_id);

		return $this->ok();
	}
}
