<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда сессия настройки TOTP истекла (пользователь слишком долго не подтверждал код)
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Totp_PendingSetupExpired extends DomainException
{
	public function __construct(string $message = "totp pending setup expired")
	{

		parent::__construct($message);
	}
}
