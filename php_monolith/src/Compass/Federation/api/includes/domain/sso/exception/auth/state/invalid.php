<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/** параметр state не валиден */
class Domain_Sso_Exception_Auth_State_Invalid extends DomainException {

	public function __construct(string $message = "state parameter is invalid") {

		parent::__construct($message);
	}
}