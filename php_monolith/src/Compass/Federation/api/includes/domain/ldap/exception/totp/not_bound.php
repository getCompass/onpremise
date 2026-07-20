<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда TOTP не привязан к пользователю
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Totp_NotBound extends DomainException
{
	public function __construct(string $message = "totp not bound")
	{

		parent::__construct($message);
	}
}
