<?php

namespace Compass\Speaker;

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
	 * функция для очистки блокировок если передан user_id чистим только по пользователю
	 *
	 * @throws \parseException|\BaseFrame\Exception\Request\ParamException
	 */
	public function clearAll():array {

		assertNotPublicServer();

		$user_id = $this->post(\Formatter::TYPE_INT, "user_id", 0);

		// проверяем, что передан корректный ID
		if ($user_id < 0) {
			return $this->error(400, "User id is incorrect");
		}

		Type_Antispam_User::clearAll($user_id);
		return $this->ok();
	}
}
