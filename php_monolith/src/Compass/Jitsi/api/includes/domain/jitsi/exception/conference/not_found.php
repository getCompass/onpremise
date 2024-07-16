<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** конференция не найдена */
class Domain_Jitsi_Exception_Conference_NotFound extends DomainException {

	public function __construct(string $message = "conference not found") {

		parent::__construct($message);
	}
}