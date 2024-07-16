<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** невалидное имя гостя */
class Domain_Guest_Exception_InvalidName extends DomainException {

	public function __construct(string $message = "invalid guest name") {

		parent::__construct($message);
	}
}