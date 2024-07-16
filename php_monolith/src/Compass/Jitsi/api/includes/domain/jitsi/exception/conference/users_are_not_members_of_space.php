<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** участники конференции не участники простанства */
class Domain_Jitsi_Exception_Conference_UsersAreNotMembersOfSpace extends DomainException {

	public function __construct(string $message = "users are not members of space") {

		parent::__construct($message);
	}
}