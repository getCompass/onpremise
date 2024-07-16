<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** достигнут лимит некорректного ввода секрета */
class Domain_User_Exception_Password_ErrorCountLimitExceeded extends DomainException {

	/** @var int время для следующей попытки */
	private int $next_attempt;

	/**
	 * Domain_User_Exception_Password_ErrorCountLimitExceeded
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