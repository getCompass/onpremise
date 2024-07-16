<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** конференция не является сингл звонком */
class Domain_Jitsi_Exception_Conference_IsNotSingle extends DomainException {

	public function __construct(string $message = "is not single") {

		parent::__construct($message);
	}
}