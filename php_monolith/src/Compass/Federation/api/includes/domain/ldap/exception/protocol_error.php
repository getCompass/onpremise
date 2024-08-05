<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * исключение связанное с ошибкой на уровне LDAP протокола
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_ProtocolError extends DomainException {

	protected int $_error_number;

	public function __construct(int $_error_number, string $message = "ldap protocol error") {

		$this->_error_number = $_error_number;
		parent::__construct($message);
	}

	public function getErrorNumber():int {

		return $this->_error_number;
	}
}