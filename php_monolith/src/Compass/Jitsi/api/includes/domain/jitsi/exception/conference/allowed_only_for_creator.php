<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** Действие доступно только для создателя конференции */
class Domain_Jitsi_Exception_Conference_AllowedOnlyForCreator extends DomainException {

	public function __construct(string $message = "action allowed only for creator") {

		parent::__construct($message);
	}
}