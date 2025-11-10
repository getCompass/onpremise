<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда 2fa авторизация отключена
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Authorization2FaDisabled extends DomainException {

	public function __construct(string $message = "authorization 2fa disabled") {

		parent::__construct($message);
	}
}