<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда не нашли запись с привязанной почтой
 *
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_UserRelNotFound extends DomainException {

	public function __construct(string $message = "mail user rel not found") {

		parent::__construct($message);
	}
}