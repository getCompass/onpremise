<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/**
 * исключение – пользователь был зарегистрирован через SSO, действие не доступно
 * @package Compass\Pivot
 */
class Domain_User_Exception_Security_UserWasRegisteredBySso extends DomainException {

	public function __construct(string $message = "user was registered by sso, action is not allowed") {

		parent::__construct($message);
	}
}