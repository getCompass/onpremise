<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда кончились подтверждения кода
 *
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_IsBeforeNextResendAt extends DomainException {

	public function __construct(string $message = "before next resend at") {

		parent::__construct($message);
	}
}