<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда не нашли почту LDAP
 *
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_LdapMailNotFound extends DomainException {

	public function __construct(string $message = "ldap mail not found") {

		parent::__construct($message);
	}
}