<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда подтверждение почты еще не завершено
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_ConfirmIsNotSuccess extends DomainException {

	public function __construct(string $message = "mail confirm is not finished") {

		parent::__construct($message);
	}
}