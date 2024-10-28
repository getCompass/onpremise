<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** Лимит на число конференций */
class Domain_Jitsi_Exception_PermanentConference_ConferenceLimit extends DomainException {

	public function __construct(string $message = "conference limit") {

		parent::__construct($message);
	}
}