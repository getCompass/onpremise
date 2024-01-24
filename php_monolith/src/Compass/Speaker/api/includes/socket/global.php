<?php

namespace Compass\Speaker;

/**
 * глобальный контроллер
 */
class Socket_Global extends \BaseFrame\Controller\Socket {

	// Поддерживаемые методы. Регистр не имеет значение */
	public const ALLOW_METHODS = [
		"getStatus",
	];

	// -------------------------------------------------------
	// WORK METHODS
	// -------------------------------------------------------

	/**
	 * возвращает название текущего модуля
	 *
	 * @return array
	 */
	public function getStatus() {

		return $this->ok([
			"module" => (string) CURRENT_MODULE,
		]);
	}
}