<?php

namespace Compass\Pivot;

use BaseFrame\Exception\DomainException;

/** количество ошибок превышено */
class Domain_User_Exception_Confirmation_Mail_ErrorCountExceeded extends DomainException {

	protected int $_next_attempt;

	public function __construct(int $next_attempt, string $message = "", int $code = 0, \Throwable $previous = null) {

		$this->_next_attempt = $next_attempt;
		parent::__construct($message);
	}

	/**
	 * Получить время для следующей попытки
	 * @return int
	 */
	public function getNextAttempt() : int {
		return $this->_next_attempt;
	}

}