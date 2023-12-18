<?php

namespace Compass\Company;

/**
 * Некорректное значение параметра entry_role
 */
class Domain_HiringRequest_Exception_IncorrectEntryRole extends \BaseFrame\Exception\DomainException {

	public function __construct(string $message = "incorrect value of entry_role") {

		parent::__construct($message);
	}
}