<?php

namespace Compass\Company;

/**
 * статус бота - отключён
 */
class Domain_Userbot_Exception_DisabledStatus extends \BaseFrame\Exception\DomainException {

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