<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** токен и подпись не совпали */
class Domain_User_Exception_AuthStory_Sso_SignatureMismatch extends DomainException {

	public function __construct(string $message = "signature mismatch") {

		parent::__construct($message);
	}
}