<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** неверный пароль */
class Domain_Jitsi_Exception_Conference_WrongPassword extends DomainException {

	public function __construct(string $message = "wrong password") {

		parent::__construct($message);
	}
}