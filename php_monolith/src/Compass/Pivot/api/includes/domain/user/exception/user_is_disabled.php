<?php

namespace Compass\Pivot;

/**
 * Пользователь деактивирован
 */
class Domain_User_Exception_UserIsDisabled extends \DomainException {

	public function __construct(string $message = "user is disabled", int $code = 0, ?Throwable $previous = null) {

		parent::__construct($message, $code, $previous);
	}
}