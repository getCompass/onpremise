<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда почта уже занята
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_IsOccupied extends DomainException {

	public function __construct(string $message = "mail is occupied") {

		parent::__construct($message);
	}
}