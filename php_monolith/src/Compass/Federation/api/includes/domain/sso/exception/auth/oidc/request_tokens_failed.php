<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/** запрос на получение токенов провалился */
class Domain_Sso_Exception_Auth_Oidc_RequestTokensFailed extends DomainException {

	public function __construct(string $message = "request tokens failed") {

		parent::__construct($message);
	}
}