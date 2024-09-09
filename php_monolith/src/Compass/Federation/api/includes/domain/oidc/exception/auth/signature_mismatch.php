<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/** не совпала подпись для попытки аутентификации */
class Domain_Oidc_Exception_Auth_SignatureMismatch extends DomainException {

	public function __construct(string $message = "signature mismatch") {

		parent::__construct($message);
	}
}