<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** пространство не найдено */
class Domain_Space_Exception_NotFound extends DomainException {

	public function __construct(string $message = "not found") {

		parent::__construct($message);
	}
}