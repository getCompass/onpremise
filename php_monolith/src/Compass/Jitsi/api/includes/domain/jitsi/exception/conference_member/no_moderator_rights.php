<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** нет прав модератора */
class Domain_Jitsi_Exception_ConferenceMember_NoModeratorRights extends DomainException {

	public function __construct(string $message = "no moderator rights") {

		parent::__construct($message);
	}
}