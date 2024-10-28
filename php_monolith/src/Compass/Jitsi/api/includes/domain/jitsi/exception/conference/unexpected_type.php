<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** Неожиданный тип */
class Domain_Jitsi_Exception_Conference_UnexpectedType extends DomainException {

	public function __construct(string $message = "unexpected type") {

		parent::__construct($message);
	}
}