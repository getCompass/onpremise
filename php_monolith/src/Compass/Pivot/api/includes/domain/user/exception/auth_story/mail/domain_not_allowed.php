<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** домен почты не разрешен для регистрации */
class Domain_User_Exception_AuthStory_Mail_DomainNotAllowed extends DomainException {

	public function __construct(string $message = "domain not allowed") {

		parent::__construct($message);
	}
}