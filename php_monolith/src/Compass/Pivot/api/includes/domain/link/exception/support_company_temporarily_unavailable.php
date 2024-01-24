<?php

namespace Compass\Pivot;

/**
 * Компания поддержки временно недоступна
 */
class Domain_Link_Exception_SupportCompanyTemporarilyUnavailable extends \DomainException {

	/**
	 * Конструктор.
	 */
	public function __construct(public int $valid_till, string $message = "", int $code = 0, ?\Throwable $previous = null) {

		parent::__construct($message, $code, $previous);
	}
}