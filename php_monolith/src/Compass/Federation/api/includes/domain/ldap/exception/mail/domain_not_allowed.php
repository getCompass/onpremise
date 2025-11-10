<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда домен почты не разрешен
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_DomainNotAllowed extends DomainException {

	public function __construct(string $message = "mail domain not allowed") {

		parent::__construct($message);
	}
}