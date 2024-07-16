<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** пользователь имеет активную конференцию */
class Domain_Jitsi_Exception_UserActiveConference_OpponentUserHaveActiveConference extends DomainException {

	public function __construct(string $message = "user have active conference") {

		parent::__construct($message);
	}
}