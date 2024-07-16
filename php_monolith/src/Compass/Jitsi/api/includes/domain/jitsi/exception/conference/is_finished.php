<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** конференция завершена */
class Domain_Jitsi_Exception_Conference_IsFinished extends DomainException {

	public function __construct(string $message = "is finished") {

		parent::__construct($message);
	}
}