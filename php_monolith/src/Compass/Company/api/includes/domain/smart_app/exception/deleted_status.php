<?php

namespace Compass\Company;

/**
 * статус приложения - удалён
 */
class Domain_SmartApp_Exception_DeletedStatus extends \BaseFrame\Exception\DomainException {

	protected array $_extra;

	public function __construct(string $message, array $extra = []) {

		$this->message = $message;
		$this->_extra  = $extra;
		parent::__construct($message);
	}

	/**
	 * вернуть экстру
	 */
	public function getExtra():array {

		return $this->_extra;
	}
}