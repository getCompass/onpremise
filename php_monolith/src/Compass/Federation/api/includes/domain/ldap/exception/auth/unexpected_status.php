<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/** попытка аутентификации имеет неожидаемый статус */
class Domain_Ldap_Exception_Auth_UnexpectedStatus extends DomainException {

	public function __construct(string $message = "auth have unexpected status") {

		parent::__construct($message);
	}
}