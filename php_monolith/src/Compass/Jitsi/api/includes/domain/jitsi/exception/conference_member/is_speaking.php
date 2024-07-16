<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** участник конференции участвует в конференции */
class Domain_Jitsi_Exception_ConferenceMember_IsSpeaking extends DomainException {

	public function __construct(string $message = "conference member is speaking") {

		parent::__construct($message);
	}
}