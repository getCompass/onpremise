<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** перенаправляем аутентфикацию на SSO */
class Domain_User_Exception_AuthStory_RedirectToSso extends DomainException {

	public function __construct(string $message = "") {

		parent::__construct($message);
	}
}