<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** Конференция с таким именем уже существует */
class Domain_Jitsi_Exception_PermanentConference_ConferenceExist extends DomainException {

	public function __construct(string $message = "conference exist") {

		parent::__construct($message);
	}
}