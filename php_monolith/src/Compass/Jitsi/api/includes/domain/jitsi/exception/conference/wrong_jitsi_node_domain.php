<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** неверный домен jitsi ноды */
class Domain_Jitsi_Exception_Conference_WrongJitsiNodeDomain extends DomainException {

	public function __construct(string $message = "wrong jitsi node domain") {

		parent::__construct($message);
	}
}