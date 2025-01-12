<?php

namespace Compass\Company;

/**
 * Валидация сессии не пройдена
 */
class Domain_Member_Exception_SessionValidationFailed extends \BaseFrame\Exception\DomainException {

	/**
	 * Валидация сессии не пройдена
	 */
	public function __construct(string $message, int $code = 0) {

		parent::__construct($message);
		$this->code = $code;
	}
}