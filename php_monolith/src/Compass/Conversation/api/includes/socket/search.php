<?php

namespace Compass\Conversation;

/**
 * Контроллер для работы с сокет-методами управления поиском.
 */
class Socket_Search extends \BaseFrame\Controller\Socket {

	// поддерживаемые методы, регистр не имеет значение
	public const ALLOW_METHODS = [
		"tryReindex",
	];

	/**
	 * Пытается запустить переиндексацию пространства.
	 */
	public function tryReindex():array {

		Domain_Search_Action_TryFullReindex::run();
		return $this->ok();
	}
}