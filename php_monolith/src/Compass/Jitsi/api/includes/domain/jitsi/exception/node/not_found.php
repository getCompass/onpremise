<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** нода не найдена */
class Domain_Jitsi_Exception_Node_NotFound extends DomainException {

	public function __construct(string $message = "node config not found") {

		parent::__construct($message);
	}
}