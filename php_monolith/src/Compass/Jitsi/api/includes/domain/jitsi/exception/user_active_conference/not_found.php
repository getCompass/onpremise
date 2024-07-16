<?php

namespace Compass\Jitsi;

use BaseFrame\Exception\DomainException;

/** запись об активной конференции пользователя не найдена */
class Domain_Jitsi_Exception_UserActiveConference_NotFound extends DomainException {

	public function __construct(string $message = "user active conference not found") {

		parent::__construct($message);
	}
}