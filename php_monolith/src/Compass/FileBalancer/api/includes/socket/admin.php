<?php

namespace Compass\FileBalancer;

/**
 * контроллер сокет методов для проверки правильности конфига
 */
class Socket_Admin extends \BaseFrame\Controller\Socket {

	// Поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"isConfigValid",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * сохранить блок с сообщениями в архив
	 *
	 * @throws parseException|paramException
	 */
	public function isConfigValid():array {

		// получаем хэш конфига с php_admin
		$config_hash = $this->post("?h", "config_hash");

		if (!Type_Node_Config::isValidConfig($config_hash)) {
			return $this->error(10015, "config is not valid");
		}

		return $this->ok();
	}
}