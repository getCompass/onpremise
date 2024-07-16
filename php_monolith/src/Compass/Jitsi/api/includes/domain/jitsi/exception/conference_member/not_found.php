<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** участник конференции не найден */
class Domain_Jitsi_Exception_ConferenceMember_NotFound extends DomainException {

	public function __construct(string $message = "conference member not found") {

		parent::__construct($message);
	}
}