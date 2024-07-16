<?php

namespace Compass\Pivot;

/**
 * Превысили число ошибок смс
 */
class Domain_User_Exception_Security_Phone_SmsErrorCountExceeded extends \BaseFrame\Exception\DomainException {

	/** @var int время для следующей попытки */
	private int $next_attempt;

	/**
	 * cs_AddPhoneSmsErrorCountExceeded constructor.
	 */
	public function __construct(int $next_attempt = 0, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->next_attempt = $next_attempt;
		parent::__construct($message, $code, $previous);
	}

	public function setNextAttempt(int $next_attempt):void {

		$this->next_attempt = $next_attempt;
	}

	public function getNextAttempt():int {

		return $this->next_attempt;
	}
}