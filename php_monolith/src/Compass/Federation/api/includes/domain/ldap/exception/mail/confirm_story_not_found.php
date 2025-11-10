<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда кончились подтверждения кода
 *
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_ConfirmStoryNotFound extends DomainException {

	public function __construct(string $message = "confirm story not found") {

		parent::__construct($message);
	}
}