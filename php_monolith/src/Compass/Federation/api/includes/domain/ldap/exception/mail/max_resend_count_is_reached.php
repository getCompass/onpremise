<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда кончились переотправки кода
 *
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_MaxResendCountIsReached extends DomainException {

	public function __construct(string $message = "max resend count is reached") {

		parent::__construct($message);
	}
}