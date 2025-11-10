<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда подтверждение почты протухло
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_ConfirmIsExpired extends DomainException {

	public function __construct(string $message = "mail confirm is expired") {

		parent::__construct($message);
	}
}