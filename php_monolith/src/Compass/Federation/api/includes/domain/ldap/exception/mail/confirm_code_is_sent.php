<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда в текущем запросе уже был отправлен код подтверждения
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_ConfirmCodeIsSent extends DomainException {

	public function __construct(string $message = "confirm code is sent") {

		parent::__construct($message);
	}
}