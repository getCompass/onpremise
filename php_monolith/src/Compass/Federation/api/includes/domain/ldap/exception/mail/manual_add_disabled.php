<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Ручная привязка почты запрещена
 *
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_ManualAddDisabled extends DomainException {

	public function __construct(string $message = "manual mail add disabled") {

		parent::__construct($message);
	}
}