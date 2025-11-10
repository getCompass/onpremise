<?php

namespace Compass\Federation;

use BaseFrame\Exception\DomainException;

/**
 * Исключение, когда кончились подтверждения кода
 *
 * @package Compass\Federation
 */
class Domain_Ldap_Exception_Mail_MaxErrorCountIsReached extends DomainException {

	private int $expires_at;

	public function __construct(int $expires_at, string $message = "max error count is reached") {

		$this->expires_at = $expires_at;
		parent::__construct($message);
	}

	/**
	 * Вернуть время истечения попытки
	 * @return int
	 */
	public function getExpiresAt():int {

		return $this->expires_at;
	}
}