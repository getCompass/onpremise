<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда TOTP код неверный
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Totp_CodeIsIncorrect extends DomainException
{
	public function __construct(string $message = "totp code is incorrect")
	{

		parent::__construct($message);
	}
}
