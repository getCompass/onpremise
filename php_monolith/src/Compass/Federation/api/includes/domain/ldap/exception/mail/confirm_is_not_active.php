<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда подтверждение почты еще уже завершено
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_ConfirmIsNotActive extends DomainException {

	public function __construct(string $message = "mail confirm is not active") {

		parent::__construct($message);
	}
}