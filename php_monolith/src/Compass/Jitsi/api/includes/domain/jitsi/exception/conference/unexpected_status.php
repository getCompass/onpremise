<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** неожиданный статус */
class Domain_Jitsi_Exception_Conference_UnexpectedStatus extends DomainException {

	public function __construct(string $message = "unexpected status") {

		parent::__construct($message);
	}
}