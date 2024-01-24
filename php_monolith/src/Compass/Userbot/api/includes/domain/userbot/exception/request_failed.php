<?php

namespace Compass\Userbot;

/**
 * выполнено запроса для бота провалилось
 */
class Domain_Userbot_Exception_RequestFailed extends \BaseFrame\Exception\DomainException {

	protected array $_failed_data;

	public function __construct(string $message, array $data = []) {

		$this->message      = $message;
		$this->_failed_data = $data;
		parent::__construct($message);
	}

	/**
	 * вернуть данные провала запроса
	 */
	public function getFailedData():array {

		return $this->_failed_data;
	}
}