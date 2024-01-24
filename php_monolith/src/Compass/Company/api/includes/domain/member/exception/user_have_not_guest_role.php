<?php

namespace Compass\Company;

/**
 * Исключение – пользователь не имеет роль гостя
 */
class Domain_Member_Exception_UserHaveNotGuestRole extends \BaseFrame\Exception\DomainException {

	public function __construct(string $message = "user have not guest role") {

		parent::__construct($message);
	}
}