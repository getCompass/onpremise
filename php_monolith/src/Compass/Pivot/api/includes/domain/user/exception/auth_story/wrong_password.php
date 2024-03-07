<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** ввели некорректный пароль */
class Domain_User_Exception_AuthStory_WrongPassword extends DomainException {

	private int $available_attempts;

	public function __construct(int $available_attempts = 0, string $message = "") {

		$this->available_attempts = $available_attempts;
		parent::__construct($message);
	}

	public function getAvailableAttempts():int {

		return $this->available_attempts;
	}
}