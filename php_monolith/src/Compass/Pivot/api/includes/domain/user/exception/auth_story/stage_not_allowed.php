<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** данный этап аутентификации не доступен */
class Domain_User_Exception_AuthStory_StageNotAllowed extends DomainException {

	public function __construct(string $message = "stage not allowed") {

		parent::__construct($message);
	}
}