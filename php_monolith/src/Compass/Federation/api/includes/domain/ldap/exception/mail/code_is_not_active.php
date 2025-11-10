<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, что код недействительный
 *
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_CodeIsNotActive extends DomainException {

	public function __construct(string $message = "code is not active") {

		parent::__construct($message);
	}
}