<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * исключение, когда @see Domain_Ldap_Entity_Client_Interface::bind() завершется провалом
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Auth_BindFailed extends DomainException {

	public function __construct(string $message = "bind failed") {

		parent::__construct($message);
	}
}