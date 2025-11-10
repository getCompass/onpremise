<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * 2FA отключен
 */
class Domain_Ldap_Exception_Auth_2faDisabled extends DomainException {

	public function __construct(string $message = "2fa disabled") {

		parent::__construct($message);
	}
}