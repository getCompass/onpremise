<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/** попытка аутентификации через ldap не найдена */
class Domain_Ldap_Exception_Auth_TokenNotFound extends DomainException {

	public function __construct(string $message = "auth not found") {

		parent::__construct($message);
	}
}