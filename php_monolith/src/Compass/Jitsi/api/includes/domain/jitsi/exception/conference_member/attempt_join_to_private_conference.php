<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** попытка вступить в приватную конференцию */
class Domain_Jitsi_Exception_ConferenceMember_AttemptJoinToPrivateConference extends DomainException {

	public function __construct(string $message = "attempt join to private conference") {

		parent::__construct($message);
	}
}