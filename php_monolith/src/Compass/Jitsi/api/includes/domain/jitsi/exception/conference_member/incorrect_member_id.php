<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** получили некорректный ID участника jitsi конференции */
class Domain_Jitsi_Exception_ConferenceMember_IncorrectMemberId extends DomainException {

	public function __construct(string $message = "incorrect member id") {

		parent::__construct($message);
	}
}