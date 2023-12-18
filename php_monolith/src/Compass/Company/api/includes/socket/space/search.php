<?php

namespace Compass\Company;

/**
 * Контроллер управления поиском.
 */
class Socket_Space_Search extends \BaseFrame\Controller\Socket {

	// список доступных методов
	public const ALLOW_METHODS = [
		"tryReindex",
	];

	/**
	 * Пытается запустить индексацию пространства.
	 */
	public function tryReindex():array {

		Domain_Space_Scenario_Socket::tryReindex();
		return $this->ok();
	}
}