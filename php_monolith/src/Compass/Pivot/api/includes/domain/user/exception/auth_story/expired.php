<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** попытка аутентификации протухла */
class Domain_User_Exception_AuthStory_Expired extends DomainException {

	public function __construct(string $message = "expired") {

		parent::__construct($message);
	}
}