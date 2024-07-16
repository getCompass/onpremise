<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** некорректная ссылка на конференцию */
class Domain_Jitsi_Exception_ConferenceLink_IncorrectLink extends DomainException {

	public function __construct(string $message = "incorrect conference link") {

		parent::__construct($message);
	}
}