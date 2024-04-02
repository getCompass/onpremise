<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** способ аутентификации отключен */
class Domain_User_Exception_AuthMethodDisabled extends DomainException {

	public function __construct(string $message = "auth method disabled") {

		parent::__construct($message);
	}
}