<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** Конференция удалена */
class Domain_Jitsi_Exception_PermanentConference_ConferenceIsDeleted extends DomainException {

	public function __construct(string $message = "conference deleted") {

		parent::__construct($message);
	}
}