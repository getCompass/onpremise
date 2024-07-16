<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** дублирование conference_id */
class Domain_Jitsi_Exception_Conference_ConferenceIdDuplication extends DomainException {

	public function __construct(string $message = "conference_id duplication") {

		parent::__construct($message);
	}
}