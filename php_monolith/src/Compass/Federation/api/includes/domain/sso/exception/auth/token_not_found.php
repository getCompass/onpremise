<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/** попытка аутентификации через sso не найдена */
class Domain_Sso_Exception_Auth_TokenNotFound extends DomainException {

	public function __construct(string $message = "auth not found") {

		parent::__construct($message);
	}
}