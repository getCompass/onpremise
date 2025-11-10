<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда получили некорректнуб почту с LDAP
 *
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_IncorrectLdapMail extends DomainException {

	public function __construct(string $message = "incorrect ldap mail") {

		parent::__construct($message);
	}
}