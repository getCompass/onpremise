<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** неожиданное поведение */
class Domain_User_Exception_AuthStory_Sso_UnexpectedBehaviour extends DomainException {

	public function __construct(string $message = "unexpected behaviour") {

		parent::__construct($message);
	}
}