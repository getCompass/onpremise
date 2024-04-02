<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/** попытка аутентификации истекла */
class Domain_Sso_Exception_Auth_Expired extends DomainException {

	public function __construct(string $message = "auth is expired") {

		parent::__construct($message);
	}
}