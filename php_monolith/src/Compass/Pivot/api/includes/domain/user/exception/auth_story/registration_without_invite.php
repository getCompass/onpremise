<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** регистрация не разрешена */
class Domain_User_Exception_AuthStory_RegistrationWithoutInvite extends DomainException {

	public function __construct(string $message = "registration without invite") {

		parent::__construct($message);
	}
}