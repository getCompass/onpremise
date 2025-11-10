<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когданаходися на неверном этапе
 *
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_StageIsInvalid extends DomainException {

	public function __construct(string $message = "stage is invalid") {

		parent::__construct($message);
	}
}