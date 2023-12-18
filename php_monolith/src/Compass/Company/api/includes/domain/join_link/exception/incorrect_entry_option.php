<?php

namespace Compass\Company;

/**
 * некорректное значение entry_option
 */
class Domain_JoinLink_Exception_IncorrectEntryOption extends \BaseFrame\Exception\DomainException {

	public function __construct() {

		parent::__construct("passed incorrect value of entry_option");
	}
}
