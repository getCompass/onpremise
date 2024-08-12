<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** получены некорректные Имя Фамилия из SSO провайдера */
class Domain_User_Exception_AuthStory_Sso_IncorrectFullName extends DomainException {

	public function __construct(string $message = "incorrect full_name") {

		parent::__construct($message);
	}
}