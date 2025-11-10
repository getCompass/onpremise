<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда код подтверждения не найден
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_ConfirmCodeNotFound extends DomainException {

	public function __construct(string $message = "confirm code not found") {

		parent::__construct($message);
	}
}