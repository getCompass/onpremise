<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** создатель конференции не участник простанства */
class Domain_Jitsi_Exception_Conference_CreatorIsNotMemberOfSpace extends DomainException {

	public function __construct(string $message = "creator is not member of space") {

		parent::__construct($message);
	}
}