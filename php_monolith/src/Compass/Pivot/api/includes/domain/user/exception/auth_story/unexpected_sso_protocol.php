<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** не ожиданный sso протокол */
class Domain_User_Exception_AuthStory_UnexpectedSsoProtocol extends DomainException {

	public function __construct(string $message = "unexpected sso protocol") {

		parent::__construct($message);
	}
}