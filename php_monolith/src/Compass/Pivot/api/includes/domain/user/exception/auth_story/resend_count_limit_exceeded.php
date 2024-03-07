<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** достигнут лимит переотправки */
class Domain_User_Exception_AuthStory_ResendCountLimitExceeded extends DomainException {

	protected int $_next_attempt;

	public function __construct(int $next_attempt, string $message = "resend count limit exceeded") {

		$this->_next_attempt = $next_attempt;
		parent::__construct($message);
	}

	/**
	 * @return int
	 */
	public function getNextAttempt():int {

		return $this->_next_attempt;
	}
}