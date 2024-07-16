<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** неожиданный статус пространства */
class Domain_Space_Exception_UnexpectedStatus extends DomainException {

	public function __construct(string $message = "unexpected status") {

		parent::__construct($message);
	}
}