<?php

namespace Compass\Company;

/**
 * контроллер для работы учатниками пространства
 */
class Socket_Space_Member extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"updatePermissions",
	];

	/**
	 * Обновляем права в пространстве
	 *
	 * @return array
	 * @throws \BaseFrame\Exception\Domain\ParseFatalException
	 * @throws \busException
	 * @throws \parseException
	 * @throws \queryException
	 */
	public function updatePermissions():array {

		Domain_Member_Scenario_Socket::updatePermissions();

		return $this->ok();
	}
}