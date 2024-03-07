<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** тип аутентификации не совпал с переданным */
class Domain_User_Exception_AuthStory_TypeMismatch extends DomainException {

	public function __construct(string $message = "type mismatch") {

		parent::__construct($message);
	}

}