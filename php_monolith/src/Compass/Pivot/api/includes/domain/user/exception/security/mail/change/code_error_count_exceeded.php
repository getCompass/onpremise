<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** число ошибок превысило */
class Domain_User_Exception_Security_Mail_Change_CodeErrorCountExceeded extends DomainException {

	/** @var int время для следующей попытки */
	private int $next_attempt;

	/**
	 * Domain_User_Exception_Security_Mail_Change_CodeErrorCountExceeded
	 */
	public function __construct(int $next_attempt = 0, string $message = "") {

		$this->next_attempt = $next_attempt;
		parent::__construct($message);
	}

	public function setNextAttempt(int $next_attempt):void {

		$this->next_attempt = $next_attempt;
	}

	public function getNextAttempt():int {

		return $this->next_attempt;
	}
}