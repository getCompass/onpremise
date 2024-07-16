<?php

namespace Compass\Pivot;

/**
 *  Не достигли времени переотправки
 */
class Domain_User_Exception_Mail_CodeResendNotAvailable extends \BaseFrame\Exception\DomainException {

	/** @var int время для следующей попытки */
	private int $next_attempt;

	/**
	 * Domain_User_Exception_Mail_CodeResendNotAvailable constructor.
	 *
	 * @param int $next_attempt
	 */
	public function __construct(int $next_attempt) {

		$this->next_attempt = $next_attempt;
		parent::__construct($next_attempt);
	}

	/**
	 * @return int
	 */
	public function getNextAttempt():int {

		return $this->next_attempt;
	}

}